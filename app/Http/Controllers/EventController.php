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
        $events = Event::orderBy('start_date', 'desc')->paginate(20);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $this->mergeLegacyPayload($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:exam,meeting,holiday,sports,cultural,parent_meeting,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'class_id' => 'nullable|exists:classes,id',
            'target_audience' => 'required|array',
            'target_audience.*' => 'in:all,admin,teacher,student,parent',
            'is_public' => 'boolean',
            'is_published' => 'boolean'
        ]);

        $validated['created_by'] = Auth::id();
        
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
        $this->mergeLegacyPayload($request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:exam,meeting,holiday,sports,cultural,parent_meeting,other',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'class_id' => 'nullable|exists:classes,id',
            'target_audience' => 'required|array',
            'target_audience.*' => 'in:all,admin,teacher,student,parent',
            'is_public' => 'boolean',
            'is_published' => 'boolean'
        ]);
        
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
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->paginate(20);

        return view('parent.events.index', compact('events'));
    }

    private function mergeLegacyPayload(Request $request): void
    {
        $startDate = $request->input('start_date');

        if (! $startDate && $request->filled('event_date')) {
            $eventDate = $request->input('event_date');
            $eventTime = $request->input('event_time', '08:00');
            $startDate = trim("{$eventDate} {$eventTime}");
        }

        $request->merge([
            'start_date' => $startDate,
            'type' => $request->input('type', $request->input('event_type')),
            'is_public' => $request->boolean('is_public', true),
        ]);
    }
}
