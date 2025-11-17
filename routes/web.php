<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Teacher\GradeController;
use App\Http\Controllers\Teacher\AbsenceController;
use App\Http\Controllers\Student\StudentGradeController;
use App\Http\Controllers\Student\ReportCardController;
use App\Http\Controllers\Parent\ChildrenController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/password/reset', [AuthController::class, 'showPasswordResetForm'])->name('password.reset');
    Route::post('/password/reset', [AuthController::class, 'resetPassword']);
    
    // Dashboard route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin routes
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // User management
        Route::resource('users', UserController::class);
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('/users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulk-delete');
        
        // Class management
        Route::resource('classes', ClassController::class);
        Route::post('/classes/{class}/assign-students', [ClassController::class, 'assignStudents'])->name('classes.assign-students');
        Route::post('/classes/{class}/assign-subjects', [ClassController::class, 'assignSubjects'])->name('classes.assign-subjects');
        
        // Subject management
        Route::resource('subjects', SubjectController::class);
        
        // Events management
        Route::resource('events', EventController::class);
        
        // Grades management (admin view)
        Route::get('/grades', [UserController::class, 'gradesIndex'])->name('grades.index');
        
        // Absences management (admin view)
        Route::get('/absences', [UserController::class, 'absencesIndex'])->name('absences.index');
        
        // Reports
        Route::get('/reports', [UserController::class, 'reportsIndex'])->name('reports.index');
    });
    
    // Teacher routes
    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Grade management
        Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
        Route::get('/grades/class/{class}', [GradeController::class, 'byClass'])->name('grades.by-class');
        Route::get('/grades/create', [GradeController::class, 'create'])->name('grades.create');
        Route::post('/grades', [GradeController::class, 'store'])->name('grades.store');
        Route::get('/grades/{grade}/edit', [GradeController::class, 'edit'])->name('grades.edit');
        Route::put('/grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
        Route::delete('/grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');
        Route::post('/grades/batch', [GradeController::class, 'batchEntry'])->name('grades.batch');
        
        // Absence management
        Route::get('/absences', [AbsenceController::class, 'index'])->name('absences.index');
        Route::get('/absences/class/{class}', [AbsenceController::class, 'byClass'])->name('absences.by-class');
        Route::get('/absences/create', [AbsenceController::class, 'create'])->name('absences.create');
        Route::post('/absences', [AbsenceController::class, 'store'])->name('absences.store');
        Route::post('/absences/batch', [AbsenceController::class, 'batchEntry'])->name('absences.batch');
        Route::post('/absences/{absence}/justify', [AbsenceController::class, 'justify'])->name('absences.justify');
        
        // Student profiles
        Route::get('/students/{student}', [GradeController::class, 'studentProfile'])->name('students.profile');
        
        // My classes
        Route::get('/classes', [GradeController::class, 'myClasses'])->name('classes');
    });
    
    // Student routes
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Grades
        Route::get('/grades', [StudentGradeController::class, 'index'])->name('grades.index');
        Route::get('/grades/subject/{subject}', [StudentGradeController::class, 'bySubject'])->name('grades.by-subject');
        
        // Report cards
        Route::get('/report-cards', [ReportCardController::class, 'index'])->name('report-cards.index');
        Route::get('/report-cards/{reportCard}', [ReportCardController::class, 'show'])->name('report-cards.show');
        Route::get('/report-cards/{reportCard}/download', [ReportCardController::class, 'download'])->name('report-cards.download');
        
        // Absences
        Route::get('/absences', [StudentGradeController::class, 'absences'])->name('absences');
        
        // Events
        Route::get('/events', [EventController::class, 'studentEvents'])->name('events');
    });
    
    // Parent routes
    Route::middleware(['role:parent'])->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Children management
        Route::get('/children', [ChildrenController::class, 'index'])->name('children.index');
        Route::get('/children/{child}', [ChildrenController::class, 'show'])->name('children.show');
        Route::get('/children/{child}/grades', [ChildrenController::class, 'grades'])->name('children.grades');
        Route::get('/children/{child}/absences', [ChildrenController::class, 'absences'])->name('children.absences');
        Route::get('/children/{child}/report-cards', [ChildrenController::class, 'reportCards'])->name('children.report-cards');
        Route::get('/children/{child}/report-cards/{reportCard}', [ChildrenController::class, 'viewReportCard'])->name('children.report-card');
        
        // Events
        Route::get('/events', [EventController::class, 'parentEvents'])->name('events');
    });
    
    // Shared routes for authenticated users
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::get('/profile/show', [AuthController::class, 'profile'])->name('profile.show');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
});
