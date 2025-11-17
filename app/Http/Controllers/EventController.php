<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    // Admin methods
    public function index()
    {
        $events = Event::orderBy('event_date', 'desc')->paginate(20);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'event_type' => 'required|string|max:255',
            'target_audience' => 'required|array',
            'target_audience.*' => 'in:all,admin,teacher,student,parent',
            'is_mandatory' => 'boolean',
            'is_published' => 'boolean'
        ]);

        $validated['created_by'] = Auth::id();
        $validated['target_audience'] = json_encode($validated['target_audience']);
        
        Event::create($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully.');
    }

    public function show(Event $event)
    {
        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'event_type' => 'required|string|max:255',
            'target_audience' => 'required|array',
            'target_audience.*' => 'in:all,admin,teacher,student,parent',
            'is_mandatory' => 'boolean',
            'is_published' => 'boolean'
        ]);

        $validated['target_audience'] = json_encode($validated['target_audience']);
        
        $event->update($validated);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event deleted successfully.');
    }

    // Student-specific events view
    public function studentEvents()
    {
        $student = auth()->user();
        $currentClass = $student->studentClass();
        
        $events = Event::where(function($query) use ($currentClass) {
                $query->whereNull('class_id')
                      ->orWhere('class_id', $currentClass ? $currentClass->id : null);
            })
            ->where('start_date', '>=', now()->subDays(7)) // Show events from last 7 days onwards
            ->orderBy('start_date', 'asc')
            ->paginate(20);

        return view('student.events.index', compact('events'));
    }

    // Parent-specific events view
    public function parentEvents()
    {
        $events = Event::where('is_published', true)
            ->where(function($query) {
                $query->whereJsonContains('target_audience', 'all')
                      ->orWhereJsonContains('target_audience', 'parent');
            })
            ->where('event_date', '>=', now())
            ->orderBy('event_date', 'asc')
            ->paginate(20);

        return view('parent.events.index', compact('events'));
    }
}
