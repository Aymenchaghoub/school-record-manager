<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\Absence;
use App\Models\Event;
use App\Models\ReportCard;
use Illuminate\Database\Seeder;
// Hash facade not needed - Laravel 11 auto-hashes with 'hashed' cast
// use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@school.com',
            'password' => 'password',
            'role' => 'admin',
            'phone' => '0123456789',
            'is_active' => true,
        ]);

        // Create default teacher account for testing
        $defaultTeacher = User::create([
            'name' => 'Teacher User',
            'email' => 'teacher@school.com',
            'password' => 'password',
            'role' => 'teacher',
            'phone' => '0123456788',
            'is_active' => true,
        ]);
        
        // Create additional teachers
        $teachers = [$defaultTeacher];
        for ($i = 1; $i <= 4; $i++) {
            $teachers[] = User::create([
                'name' => "Teacher $i",
                'email' => "teacher$i@school.com",
                'password' => 'password',
                'role' => 'teacher',
                'phone' => "012345678$i",
                'is_active' => true,
            ]);
        }

        // Create Classes
        $classes = [];
        $levels = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5'];
        $sections = ['A', 'B'];
        
        foreach ($levels as $level) {
            foreach ($sections as $section) {
                $classes[] = ClassModel::create([
                    'name' => "$level - Section $section",
                    'code' => str_replace(' ', '', $level) . $section,
                    'level' => $level,
                    'section' => $section,
                    'academic_year' => '2024-2025',
                    'responsible_teacher_id' => $teachers[array_rand($teachers)]->id,
                    'capacity' => 30,
                    'description' => "Class for $level Section $section",
                    'is_active' => true,
                ]);
            }
        }

        // Create Subjects
        $subjects = [];
        $subjectData = [
            ['name' => 'Mathematics', 'code' => 'MATH', 'credits' => 4],
            ['name' => 'English', 'code' => 'ENG', 'credits' => 3],
            ['name' => 'Science', 'code' => 'SCI', 'credits' => 3],
            ['name' => 'History', 'code' => 'HIST', 'credits' => 2],
            ['name' => 'Geography', 'code' => 'GEO', 'credits' => 2],
            ['name' => 'Physical Education', 'code' => 'PE', 'credits' => 1],
            ['name' => 'Art', 'code' => 'ART', 'credits' => 1],
            ['name' => 'Music', 'code' => 'MUS', 'credits' => 1],
        ];

        foreach ($subjectData as $data) {
            $subjects[] = Subject::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'credits' => $data['credits'],
                'type' => 'core',
                'description' => "{$data['name']} curriculum",
                'is_active' => true,
            ]);
        }

        // Assign subjects to classes with teachers
        foreach ($classes as $class) {
            foreach ($subjects as $index => $subject) {
                $teacher = $teachers[$index % count($teachers)];
                $class->subjects()->attach($subject->id, [
                    'teacher_id' => $teacher->id,
                    'hours_per_week' => rand(2, 4),
                    'room' => 'Room ' . rand(101, 205),
                ]);
            }
        }

        // Create default student account for testing
        $defaultStudent = User::create([
            'name' => 'Student User',
            'email' => 'student@school.com',
            'password' => 'password',
            'role' => 'student',
            'phone' => '0123456787',
            'date_of_birth' => now()->subYears(15),
            'gender' => 'male',
            'address' => 'Student Address',
            'is_active' => true,
        ]);
        
        // Create default parent account for testing
        $defaultParent = User::create([
            'name' => 'Parent User',
            'email' => 'parent@school.com',
            'password' => 'password',
            'role' => 'parent',
            'phone' => '0123456786',
            'address' => 'Parent Address',
            'is_active' => true,
        ]);
        
        // Link default parent to default student
        $defaultParent->parentChildren()->attach($defaultStudent->id, [
            'relationship' => 'guardian',
            'is_primary_contact' => true,
        ]);
        
        // Enroll default student in first class
        if (!empty($classes)) {
            $defaultStudent->studentClasses()->attach($classes[0]->id, [
                'enrollment_date' => now()->subMonths(3),
                'status' => 'active',
            ]);
        }
        
        // Create Students and Parents
        $students = [$defaultStudent];
        $parents = [$defaultParent];
        
        foreach ($classes as $classIndex => $class) {
            // Create 15-20 students per class
            $studentCount = rand(15, 20);
            
            for ($i = 1; $i <= $studentCount; $i++) {
                $studentNumber = ($classIndex * 100) + $i;
                
                // Create student
                $studentEmail = 'student' . $studentNumber . '@school.com';
                // Check if user already exists
                if (!User::where('email', $studentEmail)->exists()) {
                    $student = User::create([
                        'name' => "Student {$studentNumber}",
                        'email' => $studentEmail,
                        'password' => 'password',
                        'role' => 'student',
                        'phone' => "0133{$studentNumber}",
                        'date_of_birth' => now()->subYears(rand(6, 18)),
                        'gender' => ['male', 'female'][rand(0, 1)],
                        'address' => "Address {$studentNumber}",
                        'is_active' => true,
                    ]);
                    $students[] = $student;
                }
                
                // Enroll student in class
                if (isset($student)) {
                    $student->studentClasses()->attach($class->id, [
                        'enrollment_date' => now()->subMonths(rand(1, 6)),
                        'status' => 'active',
                    ]);
                }
                
                // Create parent (every 2nd student shares a parent with previous - siblings)
                if ($i % 2 == 1 || $i == 1) {
                    $parent = User::create([
                        'name' => "Parent of Student {$studentNumber}",
                        'email' => "parent{$studentNumber}@school.com",
                        'password' => 'password',
                        'role' => 'parent',
                        'phone' => "0144{$studentNumber}",
                        'address' => "Parent Address {$studentNumber}",
                        'is_active' => true,
                    ]);
                    $parents[] = $parent;
                } else {
                    // Use previous parent (for siblings)
                    $parent = end($parents);
                }
                
                // Link parent to student
                if (isset($student) && isset($parent)) {
                    $parent->parentChildren()->attach($student->id, [
                        'relationship' => ['father', 'mother'][rand(0, 1)],
                        'is_primary_contact' => true,
                    ]);
                }
            }
        }

        // Create Grades for Students
        $gradeTypes = ['exam', 'quiz', 'assignment', 'project', 'midterm', 'final'];
        $terms = ['Term 1', 'Term 2', 'Term 3'];
        $comments = [
            'excellent' => ['Excellent work!', 'Outstanding performance!', 'Keep it up!'],
            'good' => ['Good job!', 'Well done!', 'Nice work!'],
            'average' => ['Keep practicing!', 'Room for improvement.', 'Fair effort.'],
            'poor' => ['Needs more focus.', 'Requires extra attention.', 'Please see me after class.'],
        ];
        
        foreach ($students as $student) {
            $studentClass = $student->studentClass();
            if (!$studentClass) continue;
            
            $classSubjects = $studentClass->subjects;
            
            // Give each student a performance profile (excellent, good, average, struggling)
            $performanceProfile = ['excellent', 'good', 'average', 'struggling'][rand(0, 3)];
            $gradeRanges = [
                'excellent' => [85, 100],
                'good' => [70, 90],
                'average' => [60, 80],
                'struggling' => [50, 70],
            ];
            
            foreach ($classSubjects as $subject) {
                // Some variation per subject (+/- 10 points)
                $subjectModifier = rand(-10, 10);
                
                foreach ($terms as $term) {
                    // Create 3-5 grades per subject per term
                    $gradeCount = rand(3, 5);
                    
                    for ($g = 0; $g < $gradeCount; $g++) {
                        $baseRange = $gradeRanges[$performanceProfile];
                        $gradeValue = rand($baseRange[0], $baseRange[1]) + $subjectModifier;
                        $gradeValue = max(50, min(100, $gradeValue)); // Keep within bounds
                        
                        $commentCategory = $gradeValue >= 85 ? 'excellent' : 
                                         ($gradeValue >= 70 ? 'good' : 
                                         ($gradeValue >= 60 ? 'average' : 'poor'));
                        
                        Grade::create([
                            'student_id' => $student->id,
                            'subject_id' => $subject->id,
                            'class_id' => $studentClass->id,
                            'teacher_id' => $subject->pivot->teacher_id,
                            'value' => $gradeValue,
                            'max_value' => 100,
                            'type' => $gradeTypes[array_rand($gradeTypes)],
                            'title' => ucfirst($gradeTypes[array_rand($gradeTypes)]) . " " . ($g + 1),
                            'grade_date' => now()->subDays(rand(1, 90)),
                            'term' => $term,
                            'weight' => rand(1, 3),
                            'comment' => rand(0, 2) ? $comments[$commentCategory][array_rand($comments[$commentCategory])] : null,
                        ]);
                    }
                }
            }
            
            // Create Absences (weekdays only)
            $absenceCount = rand(0, 10);
            for ($a = 0; $a < $absenceCount; $a++) {
                // Generate a weekday date only
                $absenceDate = now()->subDays(rand(1, 90));
                // Keep generating until we get a weekday
                while (in_array($absenceDate->dayOfWeek, [0, 6])) { // 0=Sunday, 6=Saturday
                    $absenceDate = now()->subDays(rand(1, 90));
                }
                
                Absence::create([
                    'student_id' => $student->id,
                    'class_id' => $studentClass->id,
                    'subject_id' => rand(0, 1) ? $classSubjects->random()->id : null,
                    'recorded_by' => $teachers[array_rand($teachers)]->id,
                    'absence_date' => $absenceDate,
                    'is_justified' => rand(0, 1) == 1,
                    'type' => ['full_day', 'partial', 'late_arrival'][rand(0, 2)],
                    'reason' => ['Sick', 'Family emergency', 'Medical appointment'][rand(0, 2)],
                ]);
            }
            
            // Generate Report Cards
            foreach ($terms as $term) {
                ReportCard::generate(
                    $student->id,
                    $studentClass->id,
                    $term,
                    '2024-2025'
                );
            }
        }

        // Create Events
        $eventTypes = ['exam', 'meeting', 'holiday', 'sports', 'cultural', 'parent_meeting'];
        
        for ($e = 0; $e < 20; $e++) {
            $startDate = now()->addDays(rand(-30, 60));
            Event::create([
                'title' => "School Event " . ($e + 1),
                'description' => "Description for event " . ($e + 1),
                'type' => $eventTypes[array_rand($eventTypes)],
                'start_date' => $startDate,
                'end_date' => rand(0, 1) ? $startDate->copy()->addHours(rand(1, 4)) : null,
                'location' => ['Main Hall', 'Sports Field', 'Auditorium', 'Classroom'][rand(0, 3)],
                'class_id' => rand(0, 1) ? $classes[array_rand($classes)]->id : null,
                'created_by' => $admin->id,
                'is_public' => true,
                'color' => ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'][rand(0, 3)],
            ]);
        }
    }
}
