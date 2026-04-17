# SchoolSphere

SchoolSphere is a Laravel + React school management platform with role-based access (admin, teacher, student, parent), academic records, absences, events, and report cards.

## SPA-only Architecture
- Backend (Laravel) exposes versioned API endpoints under `api/v1/*` for authentication and business data.
- Frontend (React + Vite in `frontend/`) is the single UI entry point and consumes only these APIs.
- Web pages are routed in the SPA (`frontend/src/routes/AppRouter.jsx`), while Laravel handles data, auth, and authorization.

## Prerequisites
- PHP 8.2+
- Composer 2+
- Node.js 18+
- MySQL 8+

## Setup
1. Clone repository
```bash
git clone <your-repo-url> school-record-manager
cd school-record-manager
```

2. Install backend dependencies
```bash
composer install
```

3. Install root frontend tooling (Laravel Vite bridge)
```bash
npm install
```

4. Install SPA frontend dependencies
```bash
cd frontend
npm install
cd ..
```

5. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

## Required Environment Variables (.env)
Start from `.env.example` and make sure these values are set:

```env
APP_NAME=SchoolSphere
APP_ENV=local
APP_KEY=
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_record_manager
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_DOMAIN=localhost
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173
```

## Database Initialization
```bash
php artisan migrate:fresh --seed
```

## Run in Development
Run backend API:
```bash
php artisan serve
```

Run SPA frontend (in a second terminal):
```bash
cd frontend
npm run dev
```

## Build Frontend
```bash
cd frontend
npm run build
```

## Run Tests
Backend tests:
```bash
php artisan test
```

Frontend tests (if script exists):
```bash
cd frontend
npm run test
```

## Useful Commands
```bash
php artisan migrate:fresh --seed
php artisan test
cd frontend && npm run dev
cd frontend && npm run build
```

## CI
A dedicated frontend workflow is available in `.github/workflows/frontend.yml`.