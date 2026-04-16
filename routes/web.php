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
<?php

use Illuminate\Support\Facades\Route;

Route::view('/{any?}', 'app')->where('any', '.*');
        Route::get('/children/{child}/report-cards', [ChildrenController::class, 'reportCards'])->name('children.report-cards');
        Route::get('/children/{child}/report-cards/{reportCard}', [ChildrenController::class, 'viewReportCard'])->name('children.report-card');

        Route::get('/events', [EventController::class, 'parentEvents'])->name('events');
    });
    
    // Shared routes for authenticated users
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::get('/profile/show', [AuthController::class, 'profile'])->name('profile.show');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
});

Route::get('/app/{any?}', function () {
    return view('app');
})->where('any', '.*');
