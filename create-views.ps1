# PowerShell script to create all missing view files

$baseDir = "resources\views"

# Function to create a basic blade template
function Create-BladeFile {
    param (
        [string]$path,
        [string]$layout,
        [string]$title,
        [string]$content
    )
    
    $template = @"
@extends('$layout')

@section('title', '$title')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">$title</h3>
                </div>
                <div class="card-body">
                    $content
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
"@
    
    $fullPath = Join-Path $baseDir $path
    $directory = Split-Path $fullPath -Parent
    
    if (!(Test-Path $directory)) {
        New-Item -ItemType Directory -Path $directory -Force | Out-Null
    }
    
    if (!(Test-Path $fullPath)) {
        Set-Content -Path $fullPath -Value $template
        Write-Host "Created: $fullPath" -ForegroundColor Green
    } else {
        Write-Host "Exists: $fullPath" -ForegroundColor Yellow
    }
}

# Admin views
Create-BladeFile "admin\subjects\show.blade.php" "layouts.admin" "Subject Details" "<p>Subject details will be displayed here.</p>"
Create-BladeFile "admin\events\index.blade.php" "layouts.admin" "Events Management" "<p>Events list will be displayed here.</p>"
Create-BladeFile "admin\events\create.blade.php" "layouts.admin" "Create Event" "<p>Event creation form will be displayed here.</p>"
Create-BladeFile "admin\events\edit.blade.php" "layouts.admin" "Edit Event" "<p>Event edit form will be displayed here.</p>"
Create-BladeFile "admin\events\show.blade.php" "layouts.admin" "Event Details" "<p>Event details will be displayed here.</p>"
Create-BladeFile "admin\users\show.blade.php" "layouts.admin" "User Details" "<p>User details will be displayed here.</p>"
Create-BladeFile "admin\classes\show.blade.php" "layouts.admin" "Class Details" "<p>Class details will be displayed here.</p>"

# Teacher views
Create-BladeFile "teacher\dashboard.blade.php" "layouts.app" "Teacher Dashboard" "<h1>Welcome, Teacher!</h1><p>Dashboard content here.</p>"
Create-BladeFile "teacher\grades\index.blade.php" "layouts.app" "My Grades" "<p>Grades list will be displayed here.</p>"
Create-BladeFile "teacher\grades\by-class.blade.php" "layouts.app" "Class Grades" "<p>Class grades will be displayed here.</p>"
Create-BladeFile "teacher\grades\create.blade.php" "layouts.app" "Add Grade" "<p>Grade creation form will be displayed here.</p>"
Create-BladeFile "teacher\grades\edit.blade.php" "layouts.app" "Edit Grade" "<p>Grade edit form will be displayed here.</p>"
Create-BladeFile "teacher\absences\index.blade.php" "layouts.app" "Absences" "<p>Absences list will be displayed here.</p>"
Create-BladeFile "teacher\absences\by-class.blade.php" "layouts.app" "Class Absences" "<p>Class absences will be displayed here.</p>"
Create-BladeFile "teacher\absences\create.blade.php" "layouts.app" "Record Absence" "<p>Absence form will be displayed here.</p>"
Create-BladeFile "teacher\students\profile.blade.php" "layouts.app" "Student Profile" "<p>Student profile will be displayed here.</p>"
Create-BladeFile "teacher\classes\index.blade.php" "layouts.app" "My Classes" "<p>Classes list will be displayed here.</p>"

# Student views
Create-BladeFile "student\dashboard.blade.php" "layouts.app" "Student Dashboard" "<h1>Welcome, Student!</h1><p>Dashboard content here.</p>"
Create-BladeFile "student\grades\index.blade.php" "layouts.app" "My Grades" "<p>Your grades will be displayed here.</p>"
Create-BladeFile "student\grades\by-subject.blade.php" "layouts.app" "Subject Grades" "<p>Subject grades will be displayed here.</p>"
Create-BladeFile "student\report-cards\index.blade.php" "layouts.app" "My Report Cards" "<p>Report cards will be displayed here.</p>"
Create-BladeFile "student\report-cards\show.blade.php" "layouts.app" "Report Card" "<p>Report card details will be displayed here.</p>"
Create-BladeFile "student\report-cards\pdf.blade.php" "layouts.app" "Report Card PDF" "<p>Report card PDF template.</p>"
Create-BladeFile "student\absences\index.blade.php" "layouts.app" "My Absences" "<p>Your absences will be displayed here.</p>"
Create-BladeFile "student\events\index.blade.php" "layouts.app" "Events" "<p>Events will be displayed here.</p>"

# Parent views
Create-BladeFile "parent\dashboard.blade.php" "layouts.app" "Parent Dashboard" "<h1>Welcome, Parent!</h1><p>Dashboard content here.</p>"
Create-BladeFile "parent\children\index.blade.php" "layouts.app" "My Children" "<p>Children list will be displayed here.</p>"
Create-BladeFile "parent\children\show.blade.php" "layouts.app" "Child Details" "<p>Child details will be displayed here.</p>"
Create-BladeFile "parent\children\grades.blade.php" "layouts.app" "Child Grades" "<p>Child's grades will be displayed here.</p>"
Create-BladeFile "parent\children\absences.blade.php" "layouts.app" "Child Absences" "<p>Child's absences will be displayed here.</p>"
Create-BladeFile "parent\children\report-cards.blade.php" "layouts.app" "Child Report Cards" "<p>Child's report cards will be displayed here.</p>"
Create-BladeFile "parent\children\report-card.blade.php" "layouts.app" "Report Card" "<p>Report card details will be displayed here.</p>"
Create-BladeFile "parent\events\index.blade.php" "layouts.app" "Events" "<p>Events will be displayed here.</p>"

Write-Host "`nAll view files have been processed!" -ForegroundColor Cyan
