<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsenceApiController;
use App\Http\Controllers\Api\ClassApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\EventApiController;
use App\Http\Controllers\Api\GradeApiController;
use App\Http\Controllers\Api\ReportCardApiController;
use App\Http\Controllers\Api\SubjectApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware(['web', 'guest']);

Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/dashboard', [DashboardApiController::class, 'admin']);
        Route::apiResource('users', UserApiController::class);
        Route::apiResource('classes', ClassApiController::class);
        Route::apiResource('subjects', SubjectApiController::class);
        Route::apiResource('grades', GradeApiController::class);
        Route::apiResource('absences', AbsenceApiController::class);
        Route::apiResource('report-cards', ReportCardApiController::class);
        Route::apiResource('events', EventApiController::class);
    });

    Route::prefix('teacher')->middleware('role:teacher')->group(function () {
        Route::get('/dashboard', [DashboardApiController::class, 'teacher']);
        Route::apiResource('classes', ClassApiController::class)->only(['index', 'show']);
        Route::apiResource('subjects', SubjectApiController::class)->only(['index', 'show']);
        Route::apiResource('grades', GradeApiController::class);
        Route::apiResource('absences', AbsenceApiController::class);
        Route::apiResource('events', EventApiController::class);
    });

    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [DashboardApiController::class, 'student']);
        Route::apiResource('grades', GradeApiController::class)->only(['index', 'show']);
        Route::apiResource('absences', AbsenceApiController::class)->only(['index', 'show']);
        Route::apiResource('report-cards', ReportCardApiController::class)->only(['index', 'show']);
        Route::apiResource('events', EventApiController::class)->only(['index', 'show']);
    });

    Route::prefix('parent')->middleware('role:parent')->group(function () {
        Route::get('/dashboard', [DashboardApiController::class, 'parent']);
        Route::get('/children/grades', [GradeApiController::class, 'index']);
        Route::get('/children/grades/{grade}', [GradeApiController::class, 'show']);
        Route::get('/children/absences', [AbsenceApiController::class, 'index']);
        Route::get('/children/absences/{absence}', [AbsenceApiController::class, 'show']);
        Route::get('/children/report-cards', [ReportCardApiController::class, 'index']);
        Route::get('/children/report-cards/{report_card}', [ReportCardApiController::class, 'show']);
        Route::get('/events', [EventApiController::class, 'index']);
        Route::get('/events/{event}', [EventApiController::class, 'show']);
    });
});
