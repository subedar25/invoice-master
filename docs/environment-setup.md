# Environment Setup (Local vs Production)

Use separate env files so local and production never overwrite each other.

## Files

- Local template: `.env.local.example`
- Production template: `.env.production.example`
- Default Laravel file: `.env`

## Local setup

1. Copy local template:
   - `cp .env.local.example .env`
2. Generate key:
   - `php artisan key:generate`
3. Update local DB credentials in `.env`.

## Production setup

1. On server, create:
   - `.env.production` (from `.env.production.example`)
2. Set real production values (URL, DB, mail, cache, queue).
3. Set server environment variable:
   - `APP_ENV=production`

When `APP_ENV=production` is provided by the server process, Laravel loads the production-specific env file and keeps local `.env` separate.

## Important safety rule

- Do not upload your local `.env` to production.
- Keep `.env*` files out of git (already ignored in `.gitignore`).
