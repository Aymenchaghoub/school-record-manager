# SchoolRecordManager Architecture Overview

## Executive Summary

SchoolRecordManager is a Laravel 11-based educational management system designed to handle the complex requirements of modern educational institutions. The system provides comprehensive tools for administrators, teachers, students, and parents to manage academic records, attendance, grading, and communications.

## System Architecture

### Architectural Pattern
The application follows a **Domain-Driven MVC Architecture** with clear separation of concerns:
- **Model Layer**: Eloquent ORM models with relationships and business logic
- **View Layer**: Blade templates with component-based UI architecture
- **Controller Layer**: Thin controllers focused on request/response handling
- **Service Layer**: Complex business logic extracted to service classes (to be implemented)

### Domain Structure
The application is organized into four main domains, each with role-based access control:

1. **Admin Domain** (`/admin/*`)
   - User management (CRUD operations for all user types)
   - Class and subject management
   - System-wide grade and absence oversight
   - Event management
   - Reporting and analytics

2. **Teacher Domain** (`/teacher/*`)
   - Grade management for assigned classes
   - Absence tracking and justification
   - Student profile viewing
   - Class management

3. **Student Domain** (`/student/*`)
   - Personal grade viewing
   - Absence tracking
   - Report card access and download
   - Event participation

4. **Parent Domain** (`/parent/*`)
   - Children's academic monitoring
   - Grade and absence tracking
   - Report card access
   - School event information

## Core Components

### Models (app/Models)

| Model | Purpose | Key Relationships |
|-------|---------|------------------|
| **User** | Central authentication model with role-based access | - teacherClasses, studentClass, parentChildren |
| **ClassModel** | Academic class representation | - students, subjects, responsibleTeacher |
| **Subject** | Academic subject management | - classes, grades |
| **Grade** | Student performance tracking | - student, subject, class, teacher |
| **Absence** | Attendance management | - student, class, recordedBy |
| **ReportCard** | Periodic academic reports | - student, class, semester |
| **Event** | School events and activities | - createdBy, targetAudience |

### Controllers (app/Http/Controllers)

#### Admin Controllers
- `UserController`: Complete user lifecycle management
- `ClassController`: Class creation and student/subject assignment
- `SubjectController`: Subject CRUD operations

#### Teacher Controllers
- `GradeController`: Grade entry and management
- `AbsenceController`: Attendance tracking

#### Student Controllers
- `StudentGradeController`: Personal academic record viewing
- `ReportCardController`: Report card access and download

#### Parent Controllers
- `ChildrenController`: Multi-child academic monitoring

#### Shared Controllers
- `AuthController`: Authentication and profile management
- `DashboardController`: Role-based dashboard rendering
- `EventController`: Event management across roles

### Middleware

- **RoleMiddleware**: Enforces role-based access control
  - Validates user authentication
  - Checks role permissions
  - Verifies account activation status

## Database Schema

### Core Tables
- `users`: Multi-role user accounts with soft deletes
- `classes`: Academic classes with metadata
- `subjects`: Subject definitions with teacher assignments
- `grades`: Performance records with validation rules
- `absences`: Attendance tracking with justification support
- `report_cards`: Semester-based academic reports
- `events`: School-wide event management

### Pivot Tables
- `student_classes`: Student-class enrollments with status tracking
- `class_subjects`: Subject-class-teacher assignments
- `parent_students`: Parent-child relationships with contact priorities

## Authentication & Authorization

### Authentication Flow
1. Session-based authentication via Laravel's built-in auth
2. Password reset functionality
3. Profile management for all user types

### Authorization Strategy
- **Role-Based Access Control (RBAC)**: Four distinct roles (admin, teacher, student, parent)
- **Middleware Protection**: Route groups protected by RoleMiddleware
- **Active Status Check**: Automatic logout for deactivated accounts
- **Hierarchical Permissions**: Admins have system-wide access, others have domain-specific access

## Design Patterns & Best Practices

### Current Patterns
1. **Repository Pattern**: Implicit through Eloquent models
2. **Request Validation**: Form requests for input validation (to be implemented)
3. **Component-Based UI**: Reusable Blade components for consistency
4. **Soft Deletes**: Data preservation for audit trails

### Recommended Patterns
1. **Service Layer**: Extract complex business logic from controllers
2. **Form Requests**: Centralize validation logic
3. **Events & Listeners**: Decouple system notifications
4. **API Resources**: Standardize JSON responses (for future API)

## Frontend Architecture

### Technology Stack
- **Blade Templates**: Server-side rendering
- **TailwindCSS**: Utility-first CSS framework
- **Alpine.js**: Lightweight JavaScript framework
- **Vite**: Modern asset bundling

### Component Structure
- Base layouts for consistency
- Role-specific navigation components
- Reusable UI components (cards, tables, forms)
- Responsive design with mobile-first approach

## Security Considerations

### Implemented Security
- CSRF protection on all forms
- Password hashing via bcrypt
- Role-based access control
- Soft deletes for data recovery

### Recommended Enhancements
1. Two-factor authentication
2. Session timeout management
3. API rate limiting
4. Audit logging for sensitive operations
5. Input sanitization and XSS prevention

## Performance Optimization

### Current Optimizations
- Eager loading for relationships
- Database indexing on foreign keys
- Asset compilation and minification

### Recommended Optimizations
1. Query result caching
2. CDN integration for assets
3. Database query optimization
4. Background job processing for heavy operations

## Development Workflow

### Environment Setup
```bash
# Install dependencies
composer install
npm install

# Environment configuration
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate:fresh --seed

# Asset compilation
npm run dev

# Start development server
php artisan serve
```

### Testing Strategy
- Feature tests for user flows
- Unit tests for business logic
- Browser tests for UI interactions (to be implemented)

## Future Enhancements

### Short-term
1. API development for mobile applications
2. Real-time notifications via WebSockets
3. Advanced reporting and analytics
4. Multi-language support

### Long-term
1. Machine learning for performance predictions
2. Integration with external education platforms
3. Video conferencing for remote learning
4. Blockchain-based credential verification

## Maintenance Guidelines

### Code Standards
- PSR-12 for PHP code style
- Laravel best practices
- Comprehensive code documentation
- Meaningful commit messages

### Database Management
- Regular backups
- Migration versioning
- Seeder maintenance for development

### Monitoring
- Application performance monitoring
- Error tracking and logging
- User activity analytics
- Security audit logs

---

*Last Updated: November 2024*
*Version: 1.0.0*
