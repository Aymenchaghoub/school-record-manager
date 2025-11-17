<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $classes = ClassModel::withCount(['students', 'subjects'])
            ->with('teacher')
            ->orderBy('level')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $teachers = User::where('role', 'teacher')
            ->where('is_active', true)
            ->get();

        return view('admin.classes.create', compact('teachers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'section' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|exists:users,id',
            'academic_year' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        ClassModel::create($validated);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class created successfully.');
    }

    public function edit(ClassModel $class)
    {
        $teachers = User::where('role', 'teacher')
            ->where('is_active', true)
            ->get();

        return view('admin.classes.edit', compact('class', 'teachers'));
    }

    public function update(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'section' => 'nullable|string|max:255',
            'teacher_id' => 'nullable|exists:users,id',
            'academic_year' => 'required|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $class->update($validated);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class updated successfully.');
    }

    public function destroy(ClassModel $class)
    {
        $class->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Class deleted successfully.');
    }

    public function assignStudents(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id'
        ]);

        // Remove existing students
        \DB::table('student_classes')->where('class_id', $class->id)->delete();

        // Add new students
        foreach ($validated['student_ids'] as $studentId) {
            \DB::table('student_classes')->insert([
                'student_id' => $studentId,
                'class_id' => $class->id,
                'enrolled_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return redirect()->back()
            ->with('success', 'Students assigned to class successfully.');
    }

    public function assignSubjects(Request $request, ClassModel $class)
    {
        $validated = $request->validate([
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id'
        ]);

        // Remove existing subjects
        \DB::table('class_subjects')->where('class_id', $class->id)->delete();

        // Add new subjects
        foreach ($validated['subject_ids'] as $subjectId) {
            \DB::table('class_subjects')->insert([
                'class_id' => $class->id,
                'subject_id' => $subjectId,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return redirect()->back()
            ->with('success', 'Subjects assigned to class successfully.');
    }
}
