# UML Documentation - School Record Manager

## Domain Model Overview

The School Record Manager system is designed using a robust domain model that represents the key entities and relationships in a school environment. The model has been enhanced from the initial requirements to provide better scalability, maintainability, and data integrity.

## Key Improvements Made

1. **Single Table Inheritance for Users**: Instead of separate tables for each user type, we use a single `users` table with a `role` field. This simplifies authentication and user management while maintaining role-specific behaviors through model methods.

2. **Junction Tables for Many-to-Many Relationships**: Properly implemented junction tables for complex relationships (student-class enrollment, parent-child relationships, class-subject-teacher assignments).

3. **Soft Deletes**: Added soft delete capability to users to maintain data integrity and historical records.

4. **JSON Fields for Flexible Data**: Used JSON field for storing subject grades in report cards, allowing flexible grade structures.

5. **Enhanced Absence Tracking**: Added detailed absence types and justification mechanisms.

## Class Diagram

```plantuml
@startuml SchoolRecordManager

!define Table(name,desc) class name as "desc" << (T,#FFAAAA) >>
!define Entity(name,desc) class name as "desc" << (E,#FFCCAA) >>

' Core Entities
Entity(User, "User") {
  +id: bigint
  +name: string
  +email: string
  +password: string
  +role: enum[admin,teacher,student,parent]
  +phone: string
  +date_of_birth: date
  +gender: enum
  +address: text
  +is_active: boolean
  --
  +isAdmin(): boolean
  +isTeacher(): boolean
  +isStudent(): boolean
  +isParent(): boolean
}

Entity(ClassModel, "Class") {
  +id: bigint
  +name: string
  +code: string
  +level: string
  +section: string
  +academic_year: string
  +responsible_teacher_id: bigint
  +capacity: int
  +is_active: boolean
  --
  +getCurrentEnrollmentCount(): int
  +isFull(): boolean
}

Entity(Subject, "Subject") {
  +id: bigint
  +name: string
  +code: string
  +description: text
  +credits: int
  +type: enum[core,elective,extracurricular]
  +is_active: boolean
  --
  +getAverageGrade(): decimal
}

Entity(Grade, "Grade") {
  +id: bigint
  +student_id: bigint
  +subject_id: bigint
  +class_id: bigint
  +teacher_id: bigint
  +value: decimal
  +max_value: decimal
  +type: enum
  +grade_date: date
  +term: string
  +weight: decimal
  +comment: text
  --
  +getPercentage(): float
  +getLetterGrade(): string
}

Entity(Absence, "Absence") {
  +id: bigint
  +student_id: bigint
  +class_id: bigint
  +subject_id: bigint
  +recorded_by: bigint
  +absence_date: date
  +is_justified: boolean
  +type: enum
  +reason: string
  --
  +getDurationInHours(): float
  +canBeJustified(): boolean
}

Entity(ReportCard, "Report Card") {
  +id: bigint
  +student_id: bigint
  +class_id: bigint
  +term: string
  +academic_year: string
  +overall_average: decimal
  +total_absences: int
  +justified_absences: int
  +rank_in_class: int
  +subject_grades: json
  +conduct_grade: enum
  +is_final: boolean
  --
  +generate(): ReportCard
  +getPerformanceLabel(): string
}

Entity(Event, "Event") {
  +id: bigint
  +title: string
  +description: text
  +type: enum
  +start_date: datetime
  +end_date: datetime
  +location: string
  +class_id: bigint
  +created_by: bigint
  +is_public: boolean
  --
  +isUpcoming(): boolean
  +getDurationInDays(): int
}

' Junction Tables
Table(StudentClass, "student_classes") {
  +student_id: bigint
  +class_id: bigint
  +enrollment_date: date
  +status: enum
}

Table(ClassSubject, "class_subjects") {
  +class_id: bigint
  +subject_id: bigint
  +teacher_id: bigint
  +hours_per_week: int
  +room: string
}

Table(ParentStudent, "parent_students") {
  +parent_id: bigint
  +student_id: bigint
  +relationship: enum
  +is_primary_contact: boolean
}

' Relationships
User "1" -- "0..*" ClassModel : "responsible for"
User "0..*" -- "0..*" ClassModel : "teaches" >ClassSubject
User "0..*" -- "0..*" ClassModel : "enrolled in" >StudentClass
User "0..*" -- "0..*" User : "parent of" >ParentStudent
User "1" -- "0..*" Grade : "receives"
User "1" -- "0..*" Grade : "records"
User "1" -- "0..*" Absence : "has"
User "1" -- "0..*" ReportCard : "owns"
User "1" -- "0..*" Event : "creates"

ClassModel "1" -- "0..*" Grade : "contains"
ClassModel "1" -- "0..*" Absence : "tracks"
ClassModel "0..1" -- "0..*" Event : "hosts"
ClassModel "0..*" -- "0..*" Subject : "offers" >ClassSubject

Subject "1" -- "0..*" Grade : "assessed in"

@enduml
```

## Sequence Diagrams

### Use Case 1: Teacher Records a Grade

```plantuml
@startuml
actor Teacher
participant "Web Browser" as Browser
participant "GradeController" as Controller
participant "Grade Model" as Grade
participant "Database" as DB

Teacher -> Browser: Navigate to grades page
Browser -> Controller: GET /teacher/grades
Controller -> Grade: fetchTeacherClasses()
Grade -> DB: Query classes and students
DB -> Grade: Return data
Grade -> Controller: Return classes list
Controller -> Browser: Display grade form

Teacher -> Browser: Enter grade details
Browser -> Controller: POST /teacher/grades
Controller -> Controller: Validate input
Controller -> Grade: create(gradeData)
Grade -> DB: INSERT INTO grades
DB -> Grade: Return created grade
Grade -> Controller: Return success
Controller -> Browser: Show success message
Browser -> Teacher: Display confirmation

@enduml
```

### Use Case 2: Parent Views Child's Report Card

```plantuml
@startuml
actor Parent
participant "Web Browser" as Browser
participant "ChildrenController" as Controller
participant "User Model" as User
participant "ReportCard Model" as ReportCard
participant "Database" as DB

Parent -> Browser: Navigate to children page
Browser -> Controller: GET /parent/children
Controller -> User: getParentChildren()
User -> DB: Query parent_students
DB -> User: Return children list
User -> Controller: Return children
Controller -> Browser: Display children list

Parent -> Browser: Select child and report card
Browser -> Controller: GET /parent/children/{id}/report-cards
Controller -> ReportCard: findByStudent(childId)
ReportCard -> DB: Query report_cards
DB -> ReportCard: Return report cards
ReportCard -> Controller: Return data
Controller -> Browser: Display report cards

Parent -> Browser: View specific report card
Browser -> Controller: GET /parent/children/{id}/report-cards/{cardId}
Controller -> ReportCard: find(cardId)
ReportCard -> DB: SELECT with JSON grades
DB -> ReportCard: Return complete card
ReportCard -> Controller: Format for display
Controller -> Browser: Render report card view
Browser -> Parent: Display formatted report

@enduml
```

### Use Case 3: Admin Creates New User

```plantuml
@startuml
actor Admin
participant "Web Browser" as Browser
participant "UserController" as Controller
participant "User Model" as User
participant "Database" as DB
participant "Email Service" as Email

Admin -> Browser: Navigate to create user
Browser -> Controller: GET /admin/users/create
Controller -> Browser: Display user form

Admin -> Browser: Fill user details
Browser -> Controller: POST /admin/users
Controller -> Controller: Validate input
Controller -> User: create(userData)
User -> DB: BEGIN TRANSACTION
User -> DB: INSERT INTO users
DB -> User: Return user ID

alt If Student Role
  User -> DB: INSERT INTO student_classes
  User -> DB: INSERT INTO parent_students
end

User -> DB: COMMIT TRANSACTION
DB -> User: Transaction success
User -> Controller: Return created user
Controller -> Email: sendWelcomeEmail()
Email -> Email: Queue email
Controller -> Browser: Redirect with success
Browser -> Admin: Show success message

@enduml
```

## Activity Diagram: Grade Recording Process

```plantuml
@startuml
start
:Teacher logs in;
:Select class and subject;
:View student list;

if (Batch entry?) then (yes)
  :Display batch form;
  :Enter grades for all students;
else (no)
  :Select individual student;
  :Enter single grade;
endif

:Validate grade values;
if (Valid?) then (yes)
  :Save to database;
  :Calculate weighted average;
  :Update student statistics;
  :Log activity;
  :Display success message;
else (no)
  :Show validation errors;
  :Return to form;
endif

stop
@enduml
```

## State Diagram: Student Enrollment Status

```plantuml
@startuml
[*] --> Pending : Application submitted

Pending --> Active : Approved & Enrolled
Pending --> Rejected : Not approved

Active --> Transferred : Move to another class
Active --> Graduated : Complete program
Active --> Dropped : Leave school

Transferred --> Active : Re-enrollment
Dropped --> [*]
Graduated --> [*]
Rejected --> [*]

Active : Student attending classes
Transferred : Moved to different class/school
Graduated : Successfully completed
Dropped : Discontinued studies

@enduml
```

## Component Diagram

```plantuml
@startuml
package "Presentation Layer" {
  [Blade Views]
  [Tailwind CSS]
  [Alpine.js]
}

package "Application Layer" {
  [Controllers]
  [Middleware]
  [Form Requests]
  [Policies]
}

package "Domain Layer" {
  [Eloquent Models]
  [Business Logic]
  [Validators]
}

package "Infrastructure Layer" {
  [MySQL Database]
  [File Storage]
  [Cache]
  [Session Store]
}

[Blade Views] --> [Controllers]
[Controllers] --> [Eloquent Models]
[Eloquent Models] --> [MySQL Database]
[Middleware] --> [Session Store]
[Controllers] --> [Policies]
[Models] --> [Cache]

@enduml
```

## Design Decisions and Justifications

### 1. Role-Based Access Control (RBAC)
We implemented a simple but effective RBAC system using middleware and model methods. Each user has a single role, and access to features is controlled at the route level using custom middleware.

### 2. Grade Calculation System
Grades support different types (exam, quiz, assignment) with configurable weights. This allows teachers flexibility in how they structure their assessments while maintaining consistency in calculations.

### 3. Report Card Generation
Report cards are generated dynamically based on current grades but can be "finalized" to create permanent records. The JSON field for subject grades allows flexible storage of varying subject structures.

### 4. Event Management
Events can be school-wide or class-specific, with different visibility levels. The color field allows visual differentiation in calendar views.

### 5. Soft Deletes
Users can be soft-deleted to maintain referential integrity in historical records while removing access. This is critical for maintaining accurate academic records even after users leave the system.

## Future Enhancements

1. **Notification System**: Add real-time notifications for grades, absences, and events
2. **File Attachments**: Allow document uploads for absence justifications and assignments
3. **Advanced Analytics**: Implement predictive analytics for student performance
4. **Mobile API**: Expose RESTful APIs for mobile application development
5. **Multi-language Support**: Internationalization for different languages
6. **Audit Logging**: Comprehensive activity logging for compliance
