<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportCardController extends Controller
{
    public function index()
    {
        $student = Auth::user();
        $reportCards = ReportCard::where('student_id', $student->id)
            ->orderBy('academic_year', 'desc')
            ->orderBy('term', 'desc')
            ->paginate(10);

        return view('student.report-cards.index', compact('reportCards'));
    }

    public function show(ReportCard $reportCard)
    {
        $student = Auth::user();
        
        // Ensure this report card belongs to the student
        if ($reportCard->student_id !== $student->id) {
            abort(403, 'Unauthorized access.');
        }

        $reportCard->load(['student', 'class', 'preparedBy']);

        return view('student.report-cards.show', compact('reportCard'));
    }

    public function download(ReportCard $reportCard)
    {
        $student = Auth::user();
        
        // Ensure this report card belongs to the student
        if ($reportCard->student_id !== $student->id) {
            abort(403, 'Unauthorized access.');
        }

        $reportCard->load(['student', 'class', 'preparedBy']);

        $pdf = Pdf::loadView('student.report-cards.pdf', compact('reportCard'));
        
        $filename = sprintf(
            'report_card_%s_%s_semester_%s.pdf',
            str_replace(' ', '_', $student->name),
            $reportCard->academic_year,
            $reportCard->semester
        );

        return $pdf->download($filename);
    }
}
