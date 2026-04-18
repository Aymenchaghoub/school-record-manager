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

## Déploiement

### Backend Laravel (Railway)
1. Create a Railway project from this repository root.
2. Railway uses `railway.json` and starts Laravel with the provided start command.
3. Configure backend variables using `.env.production.example`.
4. Set `APP_URL` to your Railway public backend URL.
5. Set `FRONTEND_URL` and `SANCTUM_STATEFUL_DOMAINS` with your Vercel frontend domain and backend domain.
6. Run migrations once in Railway shell:

```bash
php artisan migrate --force
```

### Frontend React (Vercel)
1. Create a Vercel project with `frontend` as Root Directory.
2. `frontend/vercel.json` handles SPA rewrites to `index.html`.
3. If your Vercel project is set to repository root, use root `vercel.json` (already included) to build from `frontend` and publish `frontend/dist`.
4. Set frontend environment variables in Vercel:

```env
VITE_API_BASE_URL=https://your-railway-backend-domain
VITE_APP_ENV=production
VITE_REVERB_APP_KEY=your-reverb-app-key
VITE_REVERB_HOST=your-railway-backend-domain-without-https
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME=https
```

### Final Checks
1. Ensure `FRONTEND_URL` matches your Vercel URL.
2. Ensure `SANCTUM_STATEFUL_DOMAINS` includes both frontend and backend hosts.
3. Validate login, CSRF cookie flow, and realtime features after deployment.

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