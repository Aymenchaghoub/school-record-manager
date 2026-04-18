# Railway Production Variables

Use this checklist for the Laravel backend on Railway.

Do not keep placeholder values such as `your-db-host`, `your-db-username`, or `your-db-password` in production.

## Required Variables

APP_NAME=SchoolSphere
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR-RAILWAY-BACKEND.up.railway.app

APP_KEY=base64:GENERATED_KEY

DB_CONNECTION=mysql
DB_HOST=REAL_DB_HOST
DB_PORT=3306
DB_DATABASE=REAL_DB_NAME
DB_USERNAME=REAL_DB_USER
DB_PASSWORD=REAL_DB_PASSWORD

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none

SANCTUM_STATEFUL_DOMAINS=school-record-manager.vercel.app
FRONTEND_URL=https://school-record-manager.vercel.app

BROADCAST_CONNECTION=reverb
REVERB_APP_ID=schoolsphere
REVERB_APP_KEY=REAL_REVERB_KEY
REVERB_APP_SECRET=REAL_REVERB_SECRET
REVERB_HOST=YOUR-RAILWAY-BACKEND.up.railway.app
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=${PORT}

## If You Get HTTP 500

1. Check APP_KEY is not empty.
2. Check DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD are real values.
3. Run migrations in Railway shell:

```bash
php artisan migrate --force
```

4. Clear and rebuild Laravel config cache:

```bash
php artisan config:clear
php artisan config:cache
```