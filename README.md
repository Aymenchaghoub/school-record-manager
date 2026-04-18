# School Record Manager / SchoolSphere

School Record Manager (SchoolSphere) uses Laravel 11 for the backend and React as a SPA frontend.
It supports multi-role usage with admin, teacher, student, and parent access.
All core features are served through versioned API v1 endpoints consumed by the SPA.

## Prerequisites
- PHP 8.2
- Node.js 18+
- Composer
- MySQL

## Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
cd frontend && npm install && npm run build
```

## Development Run
```bash
php artisan serve
cd frontend && npm run dev
```

## Tests
```bash
php artisan test
```

## Demo Accounts
The following accounts are created by the seeders. Default password is `password`.

- Admin: admin@school.com
- Teacher: teacher@school.com
- Student: student@school.com
- Parent: parent@school.com

Additional seeded teachers and students are also generated (for example teacher1@school.com and studentXXX@school.com), all with password `password`.

## Architecture
This project follows a SPA-only architecture: React handles all user-facing navigation and screens in the frontend, while Laravel exposes business capabilities through API v1 routes (authentication, users, classes, subjects, grades, absences, events, and report cards). The SPA never relies on server-rendered pages for core workflows and communicates only with the API layer.