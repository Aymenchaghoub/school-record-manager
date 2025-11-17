<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\ClassModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsenceController extends Controller
{
    public function index()
    {
        $teacher = Auth::user();
        $absences = Absence::with(['student', 'class'])
            ->whereHas('class', function($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->orderBy('absence_date', 'desc')
            ->paginate(20);

        return view('teacher.absences.index', compact('absences'));
    }

    public function byClass(ClassModel $class)
    {
        $teacher = Auth::user();
        
        // Ensure teacher owns this class
        if ($class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $absences = Absence::with('student')
            ->where('class_id', $class->id)
            ->orderBy('absence_date', 'desc')
            ->paginate(20);

        return view('teacher.absences.by-class', compact('absences', 'class'));
    }

    public function create()
    {
        $teacher = Auth::user();
        $classes = ClassModel::where('teacher_id', $teacher->id)->get();
        $students = User::where('role', 'student')
            ->where('is_active', true)
            ->whereHas('studentClasses', function($query) use ($teacher) {
                $query->whereHas('class', function($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id);
                });
            })
            ->get();

        return view('teacher.absences.create', compact('classes', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'absence_date' => 'required|date',
            'period' => 'required|string|max:255',
            'reason' => 'nullable|string',
            'is_justified' => 'boolean'
        ]);

        $validated['marked_by'] = Auth::id();
        Absence::create($validated);

        return redirect()->route('teacher.absences.index')
            ->with('success', 'Absence recorded successfully.');
    }

    public function batchEntry(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'absence_date' => 'required|date',
            'period' => 'required|string|max:255',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id'
        ]);

        foreach ($validated['student_ids'] as $studentId) {
            Absence::create([
                'student_id' => $studentId,
                'class_id' => $validated['class_id'],
                'absence_date' => $validated['absence_date'],
                'period' => $validated['period'],
                'marked_by' => Auth::id(),
                'is_justified' => false
            ]);
        }

        return redirect()->route('teacher.absences.index')
            ->with('success', 'Absences recorded successfully.');
    }

    public function justify(Request $request, Absence $absence)
    {
        $teacher = Auth::user();
        
        // Ensure teacher owns the class for this absence
        if ($absence->class->teacher_id !== $teacher->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'justification' => 'required|string'
        ]);

        $absence->update([
            'is_justified' => true,
            'justification' => $validated['justification'],
            'justified_by' => Auth::id(),
            'justified_at' => now()
        ]);

        return redirect()->back()
            ->with('success', 'Absence justified successfully.');
    }
}
