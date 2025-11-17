# PowerShell script to check for missing views

$views = @(
    # Admin views
    "admin\dashboard.blade.php",
    "admin\users\index.blade.php",
    "admin\users\create.blade.php",
    "admin\users\edit.blade.php",
    "admin\users\show.blade.php",
    "admin\classes\index.blade.php",
    "admin\classes\create.blade.php",
    "admin\classes\edit.blade.php",
    "admin\classes\show.blade.php",
    "admin\subjects\index.blade.php",
    "admin\subjects\create.blade.php",
    "admin\subjects\edit.blade.php",
    "admin\subjects\show.blade.php",
    "admin\grades\index.blade.php",
    "admin\absences\index.blade.php",
    "admin\reports\index.blade.php",
    "admin\events\index.blade.php",
    "admin\events\create.blade.php",
    "admin\events\edit.blade.php",
    "admin\events\show.blade.php",
    
    # Teacher views
    "teacher\dashboard.blade.php",
    "teacher\grades\index.blade.php",
    "teacher\grades\by-class.blade.php",
    "teacher\grades\create.blade.php",
    "teacher\grades\edit.blade.php",
    "teacher\absences\index.blade.php",
    "teacher\absences\by-class.blade.php",
    "teacher\absences\create.blade.php",
    "teacher\students\profile.blade.php",
    "teacher\classes\index.blade.php",
    
    # Student views
    "student\dashboard.blade.php",
    "student\no-class.blade.php",
    "student\grades\index.blade.php",
    "student\grades\by-subject.blade.php",
    "student\absences\index.blade.php",
    "student\report-cards\index.blade.php",
    "student\report-cards\show.blade.php",
    "student\events\index.blade.php",
    
    # Parent views
    "parent\dashboard.blade.php",
    "parent\children\index.blade.php",
    "parent\children\show.blade.php",
    "parent\children\grades.blade.php",
    "parent\children\absences.blade.php",
    "parent\children\report-cards.blade.php",
    "parent\children\report-card.blade.php",
    "parent\events\index.blade.php",
    
    # Auth views
    "auth\login.blade.php",
    "auth\register.blade.php",
    "auth\password-reset.blade.php",
    
    # Profile view
    "profile.blade.php"
)

Write-Host "Checking for missing views..." -ForegroundColor Yellow
Write-Host ""

$missingViews = @()
$existingViews = @()
$viewsPath = "resources\views"

foreach ($view in $views) {
    $fullPath = Join-Path $viewsPath $view
    if (-Not (Test-Path $fullPath)) {
        $missingViews += $view
        Write-Host "MISSING: $view" -ForegroundColor Red
    } else {
        $existingViews += $view
        Write-Host "EXISTS: $view" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "Total views checked: $($views.Count)"
Write-Host "Existing views: $($existingViews.Count)" -ForegroundColor Green
Write-Host "Missing views: $($missingViews.Count)" -ForegroundColor Red

if ($missingViews.Count -gt 0) {
    Write-Host ""
    Write-Host "Missing views that need to be created:" -ForegroundColor Yellow
    foreach ($view in $missingViews) {
        Write-Host "  - $view"
    }
}
