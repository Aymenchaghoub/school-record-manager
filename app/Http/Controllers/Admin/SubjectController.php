<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with(['teacher', 'classes'])->paginate(20);
        
        // Get unique teachers per subject from class_subjects pivot
        foreach ($subjects as $subject) {
            $subject->assignedTeachers = \DB::table('class_subjects')
                ->where('subject_id', $subject->id)
                ->join('users', 'class_subjects.teacher_id', '=', 'users.id')
                ->select('users.id', 'users.name')
                ->distinct()
                ->get();
        }
        
        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        $teachers = User::where('role', 'teacher')->where('is_active', true)->get();
        return view('admin.subjects.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects',
            'teacher_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'credits' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        Subject::create($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function show(Subject $subject)
    {
        $subject->load('teacher', 'classes');
        return view('admin.subjects.show', compact('subject'));
    }

    public function edit(Subject $subject)
    {
        $teachers = User::where('role', 'teacher')->where('is_active', true)->get();
        return view('admin.subjects.edit', compact('subject', 'teachers'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,' . $subject->id,
            'teacher_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'credits' => 'nullable|integer|min:0',
            'is_active' => 'boolean'
        ]);

        $subject->update($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
