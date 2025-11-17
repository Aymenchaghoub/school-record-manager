# Database Schema Documentation

## Overview

The SchoolRecordManager database is designed using MySQL with InnoDB engine to ensure referential integrity through foreign key constraints. The schema follows a normalized structure to minimize data redundancy and maintain consistency.

## Database Structure

### Core Tables

1. **users** - Stores all system users (admins, teachers, students, parents)
2. **classes** - Contains information about school classes
3. **subjects** - Stores subject/course information
4. **grades** - Records student grades and assessments
5. **absences** - Tracks student attendance
6. **report_cards** - Stores generated report cards
7. **events** - Contains school events and calendar items

### Junction Tables

1. **student_classes** - Links students to their classes
2. **class_subjects** - Associates subjects with classes and assigns teachers
3. **parent_students** - Connects parents with their children

## Complete SQL DDL

```sql
-- Create Database
CREATE DATABASE IF NOT EXISTS school_record_manager
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE school_record_manager;

-- Users Table
CREATE TABLE users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student', 'parent') NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    gender ENUM('male', 'female', 'other') DEFAULT NULL,
    address TEXT DEFAULT NULL,
    profile_photo VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Password Reset Tokens Table
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions Table
CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Classes Table
CREATE TABLE classes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    level VARCHAR(100) NOT NULL,
    section VARCHAR(50) DEFAULT NULL,
    academic_year VARCHAR(50) NOT NULL,
    responsible_teacher_id BIGINT UNSIGNED DEFAULT NULL,
    capacity INT DEFAULT 30,
    description TEXT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (responsible_teacher_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_academic_year_active (academic_year, is_active),
    INDEX idx_responsible_teacher (responsible_teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Subjects Table
CREATE TABLE subjects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL,
    credits INT DEFAULT 1,
    type ENUM('core', 'elective', 'extracurricular') DEFAULT 'core',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_is_active (is_active),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Student Classes Junction Table
CREATE TABLE student_classes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('active', 'transferred', 'graduated', 'dropped') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_class (student_id, class_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Class Subjects Junction Table (with Teacher Assignment)
CREATE TABLE class_subjects (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    class_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    hours_per_week INT DEFAULT 1,
    room VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_class_subject_teacher (class_id, subject_id, teacher_id),
    INDEX idx_teacher (teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parent Students Junction Table
CREATE TABLE parent_students (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    parent_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    relationship ENUM('father', 'mother', 'guardian', 'other') DEFAULT 'guardian',
    is_primary_contact BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_parent_student (parent_id, student_id),
    INDEX idx_primary_contact (is_primary_contact)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Grades Table
CREATE TABLE grades (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NOT NULL,
    value DECIMAL(5,2) NOT NULL,
    max_value DECIMAL(5,2) DEFAULT 100,
    type ENUM('exam', 'quiz', 'assignment', 'project', 'participation', 'midterm', 'final') NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    grade_date DATE NOT NULL,
    term VARCHAR(50) DEFAULT NULL,
    weight DECIMAL(5,2) DEFAULT 1,
    comment TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_student_subject (student_id, subject_id),
    INDEX idx_class_subject (class_id, subject_id),
    INDEX idx_grade_date (grade_date),
    INDEX idx_term (term),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Absences Table
CREATE TABLE absences (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED DEFAULT NULL,
    recorded_by BIGINT UNSIGNED DEFAULT NULL,
    absence_date DATE NOT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    is_justified BOOLEAN DEFAULT FALSE,
    type ENUM('full_day', 'partial', 'late_arrival', 'early_departure') DEFAULT 'full_day',
    reason VARCHAR(255) DEFAULT NULL,
    justification TEXT DEFAULT NULL,
    justification_document VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student_date (student_id, absence_date),
    INDEX idx_class_date (class_id, absence_date),
    INDEX idx_is_justified (is_justified),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Report Cards Table
CREATE TABLE report_cards (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    term VARCHAR(50) NOT NULL,
    academic_year VARCHAR(50) NOT NULL,
    overall_average DECIMAL(5,2) DEFAULT NULL,
    total_absences INT DEFAULT 0,
    justified_absences INT DEFAULT 0,
    rank_in_class INT DEFAULT NULL,
    total_students INT DEFAULT NULL,
    subject_grades JSON DEFAULT NULL,
    principal_remarks TEXT DEFAULT NULL,
    teacher_remarks TEXT DEFAULT NULL,
    conduct_grade ENUM('Excellent', 'Very Good', 'Good', 'Fair', 'Poor') DEFAULT NULL,
    issue_date DATE NOT NULL,
    is_final BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_report_card (student_id, class_id, term, academic_year),
    INDEX idx_year_term (academic_year, term),
    INDEX idx_is_final (is_final)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events Table
CREATE TABLE events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    type ENUM('exam', 'meeting', 'holiday', 'sports', 'cultural', 'parent_meeting', 'other') NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    class_id BIGINT UNSIGNED DEFAULT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    color VARCHAR(7) DEFAULT '#3B82F6',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_dates (start_date, end_date),
    INDEX idx_type (type),
    INDEX idx_is_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Sample Data for Testing

```sql
-- Insert Admin User
INSERT INTO users (name, email, password, role, phone, is_active) VALUES
('Admin User', 'admin@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '0123456789', TRUE);

-- Insert Teachers
INSERT INTO users (name, email, password, role, phone, is_active) VALUES
('John Smith', 'teacher1@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '0123456790', TRUE),
('Jane Doe', 'teacher2@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher', '0123456791', TRUE);

-- Insert Classes
INSERT INTO classes (name, code, level, section, academic_year, responsible_teacher_id, capacity) VALUES
('Grade 1 - Section A', 'G1A', 'Grade 1', 'A', '2024-2025', 2, 30),
('Grade 1 - Section B', 'G1B', 'Grade 1', 'B', '2024-2025', 3, 30);

-- Insert Subjects
INSERT INTO subjects (name, code, credits, type) VALUES
('Mathematics', 'MATH', 4, 'core'),
('English', 'ENG', 3, 'core'),
('Science', 'SCI', 3, 'core'),
('History', 'HIST', 2, 'core'),
('Physical Education', 'PE', 1, 'core');

-- Insert Students
INSERT INTO users (name, email, password, role, phone, date_of_birth, gender, is_active) VALUES
('Alice Student', 'student1@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '0123456792', '2010-05-15', 'female', TRUE),
('Bob Student', 'student2@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', '0123456793', '2010-08-20', 'male', TRUE);

-- Insert Parents
INSERT INTO users (name, email, password, role, phone, is_active) VALUES
('Parent One', 'parent1@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', '0123456794', TRUE),
('Parent Two', 'parent2@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'parent', '0123456795', TRUE);

-- Enroll Students in Classes
INSERT INTO student_classes (student_id, class_id, enrollment_date, status) VALUES
(4, 1, '2024-09-01', 'active'),
(5, 1, '2024-09-01', 'active');

-- Link Parents to Students
INSERT INTO parent_students (parent_id, student_id, relationship, is_primary_contact) VALUES
(6, 4, 'mother', TRUE),
(7, 5, 'father', TRUE);

-- Assign Subjects to Classes with Teachers
INSERT INTO class_subjects (class_id, subject_id, teacher_id, hours_per_week, room) VALUES
(1, 1, 2, 4, 'Room 101'),
(1, 2, 2, 3, 'Room 102'),
(1, 3, 3, 3, 'Lab 1'),
(1, 4, 3, 2, 'Room 103'),
(1, 5, 2, 1, 'Gym');

-- Insert Sample Grades
INSERT INTO grades (student_id, subject_id, class_id, teacher_id, value, max_value, type, title, grade_date, term, weight) VALUES
(4, 1, 1, 2, 85, 100, 'exam', 'Mid-term Exam', '2024-10-15', 'Term 1', 2),
(4, 1, 1, 2, 92, 100, 'quiz', 'Quiz 1', '2024-09-20', 'Term 1', 1),
(5, 1, 1, 2, 78, 100, 'exam', 'Mid-term Exam', '2024-10-15', 'Term 1', 2),
(5, 2, 1, 2, 88, 100, 'assignment', 'Essay 1', '2024-09-25', 'Term 1', 1);

-- Insert Sample Absences
INSERT INTO absences (student_id, class_id, recorded_by, absence_date, is_justified, type, reason) VALUES
(4, 1, 2, '2024-10-10', TRUE, 'full_day', 'Medical appointment'),
(5, 1, 2, '2024-10-12', FALSE, 'late_arrival', 'Overslept');

-- Insert Sample Events
INSERT INTO events (title, description, type, start_date, end_date, location, created_by, is_public) VALUES
('Parent-Teacher Conference', 'Mid-semester parent meetings', 'parent_meeting', '2024-11-20 14:00:00', '2024-11-20 17:00:00', 'Main Hall', 1, TRUE),
('Math Olympiad', 'Annual mathematics competition', 'exam', '2024-12-05 09:00:00', '2024-12-05 12:00:00', 'Auditorium', 1, TRUE),
('Winter Break', 'School closed for winter holidays', 'holiday', '2024-12-20 00:00:00', '2025-01-05 23:59:59', NULL, 1, TRUE);
```

## Database Relationships

### Primary Relationships

1. **User Roles**: The `users` table uses a single table inheritance pattern with a `role` enum field to differentiate between admins, teachers, students, and parents.

2. **Class Management**: 
   - Each class has a responsible teacher (foreign key to users)
   - Students are enrolled in classes through the `student_classes` junction table
   - Subjects are assigned to classes with specific teachers through `class_subjects`

3. **Academic Records**:
   - Grades link students, subjects, classes, and teachers
   - Absences track student attendance per class
   - Report cards aggregate student performance data

4. **Family Connections**: Parents are linked to students through the `parent_students` junction table

### Cascade Rules

- Deleting a user cascades to their related records (grades, absences, enrollments)
- Deleting a class cascades to related enrollments and assignments
- Deleting a subject sets related grades' subject_id to NULL (preserving historical data)

## Indexes and Performance Optimization

The schema includes strategic indexes on:
- Foreign key columns for join performance
- Frequently queried fields (role, is_active, dates)
- Composite indexes for common query patterns
- Unique constraints to maintain data integrity

## Security Considerations

1. **Password Storage**: Uses bcrypt hashing (Laravel default)
2. **Soft Deletes**: Users table includes `deleted_at` for soft deletion
3. **Session Management**: Dedicated sessions table for secure session handling
4. **Token Management**: Password reset tokens with expiration
