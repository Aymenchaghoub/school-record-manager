<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();
        $grades = Grade::with(['student', 'subject', 'class'])
            ->whereHas('subject', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('teacher.grades.index', compact('grades'));
    }

    public function byClass(ClassModel $class)
    {
        $teacher = Auth::user();
        $grades = Grade::with(['student', 'subject'])
            ->where('class_id', $class->id)
            ->whereHas('subject', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('teacher.grades.by-class', compact('grades', 'class'));
    }

    public function create()
    {
        $teacher = Auth::user();
        
        // Get classes where the teacher teaches subjects (through class_subjects pivot table)
        $classes = ClassModel::whereHas('subjects', function($query) use ($teacher) {
            $query->where('class_subjects.teacher_id', $teacher->id);
        })->get();
        
        // Get subjects the teacher teaches (through class_subjects pivot table)
        $subjects = Subject::whereHas('classes', function($query) use ($teacher) {
            $query->where('class_subjects.teacher_id', $teacher->id);
        })->get();
        
        $students = User::where('role', 'student')->where('is_active', true)->get();

        return view('teacher.grades.create', compact('classes', 'subjects', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'exam_type' => 'required|string|max:255',
            'grade' => 'required|numeric|min:0|max:100',
            'semester' => 'required|integer|min:1|max:2',
            'academic_year' => 'required|string|max:255',
            'comment' => 'nullable|string'
        ]);

        $validated['teacher_id'] = Auth::id();
        Grade::create($validated);

        return redirect()->route('teacher.grades.index')
            ->with('success', 'Grade added successfully.');
    }

    public function edit(Grade $grade)
    {
        $teacher = Auth::user();
        
        // Ensure teacher owns this grade
        if ($grade->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $classes = ClassModel::where('teacher_id', $teacher->id)
            ->orWhereHas('subjects', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })->get();
        
        $subjects = Subject::where('teacher_id', $teacher->id)->get();
        $students = User::where('role', 'student')->where('is_active', true)->get();

        return view('teacher.grades.edit', compact('grade', 'classes', 'subjects', 'students'));
    }

    public function update(Request $request, Grade $grade)
    {
        $teacher = Auth::user();
        
        // Ensure teacher owns this grade
        if ($grade->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'exam_type' => 'required|string|max:255',
            'grade' => 'required|numeric|min:0|max:100',
            'comment' => 'nullable|string'
        ]);

        $grade->update($validated);

        return redirect()->route('teacher.grades.index')
            ->with('success', 'Grade updated successfully.');
    }

    public function destroy(Grade $grade)
    {
        $teacher = Auth::user();
        
        // Ensure teacher owns this grade
        if ($grade->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $grade->delete();

        return redirect()->route('teacher.grades.index')
            ->with('success', 'Grade deleted successfully.');
    }

    public function batchEntry(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'exam_type' => 'required|string|max:255',
            'semester' => 'required|integer|min:1|max:2',
            'academic_year' => 'required|string|max:255',
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:users,id',
            'grades.*.grade' => 'required|numeric|min:0|max:100',
            'grades.*.comment' => 'nullable|string'
        ]);

        foreach ($validated['grades'] as $gradeData) {
            Grade::create([
                'student_id' => $gradeData['student_id'],
                'subject_id' => $validated['subject_id'],
                'class_id' => $validated['class_id'],
                'teacher_id' => Auth::id(),
                'exam_type' => $validated['exam_type'],
                'grade' => $gradeData['grade'],
                'semester' => $validated['semester'],
                'academic_year' => $validated['academic_year'],
                'comment' => $gradeData['comment'] ?? null
            ]);
        }

        return redirect()->route('teacher.grades.index')
            ->with('success', 'Grades added successfully.');
    }

    public function studentProfile(User $student)
    {
        if ($student->role !== 'student') {
            abort(404);
        }

        $grades = Grade::with(['subject', 'class'])
            ->where('student_id', $student->id)
            ->where('teacher_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('teacher.students.profile', compact('student', 'grades'));
    }

    public function myClasses()
    {
        $teacher = Auth::user();
        
        // Get classes where the teacher teaches subjects (through class_subjects pivot table)
        $classes = ClassModel::withCount('students')
            ->whereHas('subjects', function($query) use ($teacher) {
                $query->where('class_subjects.teacher_id', $teacher->id);
            })
            ->with(['subjects' => function($query) use ($teacher) {
                $query->where('class_subjects.teacher_id', $teacher->id);
            }])
            ->get();

        return view('teacher.classes.index', compact('classes'));
    }
}
