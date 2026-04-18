<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsenceApiController;
use App\Http\Controllers\Api\ClassApiController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\GradeApiController;
use App\Http\Controllers\Api\ReportCardApiController;
use App\Http\Controllers\Api\SubjectApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->middleware(['web', 'guest', 'throttle:5,1']);

    Route::middleware(['web', 'auth:sanctum'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);

        Route::prefix('admin')->middleware('role:admin')->group(function () {
            Route::get('/dashboard', [DashboardApiController::class, 'admin']);
            Route::apiResource('users', UserApiController::class);
            Route::apiResource('classes', ClassApiController::class);
            Route::apiResource('subjects', SubjectApiController::class);
            Route::apiResource('grades', GradeApiController::class);
            Route::apiResource('absences', AbsenceApiController::class);
            Route::apiResource('events', EventApiController::class);
            Route::apiResource('report-cards', ReportCardApiController::class);
        });

        Route::prefix('teacher')->middleware('role:teacher')->group(function () {
            Route::get('/dashboard', [DashboardApiController::class, 'teacher']);
            Route::get('/students', [UserApiController::class, 'index']);
            Route::get('/classes', [ClassApiController::class, 'index']);
            Route::get('/subjects', [SubjectApiController::class, 'index']);
            Route::apiResource('grades', GradeApiController::class);
            Route::apiResource('absences', AbsenceApiController::class);
            Route::apiResource('events', EventApiController::class);
        });

        Route::prefix('student')->middleware('role:student')->group(function () {
            Route::get('/dashboard', [DashboardApiController::class, 'student']);
            Route::get('/grades', [GradeApiController::class, 'index']);
            Route::get('/grades/{grade}', [GradeApiController::class, 'show']);
            Route::get('/absences', [AbsenceApiController::class, 'index']);
            Route::get('/absences/{absence}', [AbsenceApiController::class, 'show']);
            Route::apiResource('report-cards', ReportCardApiController::class)->only(['index', 'show']);
        });

        Route::prefix('parent')->middleware('role:parent')->group(function () {
            Route::get('/dashboard', [DashboardApiController::class, 'parent']);
            Route::get('/children/grades', [GradeApiController::class, 'index']);
            Route::get('/children/grades/{grade}', [GradeApiController::class, 'show']);
            Route::get('/children/absences', [AbsenceApiController::class, 'index']);
            Route::get('/children/absences/{absence}', [AbsenceApiController::class, 'show']);
            Route::apiResource('report-cards', ReportCardApiController::class)->only(['index', 'show']);
            Route::get('/events', [EventApiController::class, 'parentIndex']);
        });

        Route::prefix('dashboard')->group(function () {
            Route::get('/kpis', [DashboardController::class, 'kpis']);
            Route::get('/grade-evolution', [DashboardController::class, 'gradeEvolution']);
            Route::get('/absences-per-month', [DashboardController::class, 'absencesPerMonth']);
            Route::get('/average-per-subject', [DashboardController::class, 'averagePerSubject']);
            Route::get('/students-per-class', [DashboardController::class, 'studentsPerClass']);
        });
    });
});
