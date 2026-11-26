# Inventaris FOUCreative

Laravel application for managing Fou Creative clients and the nine-step client onboarding flow.

## Requirements

- PHP 8.2+
- Composer
- Node.js 20+
- npm
- SQLite (default) or another Laravel-supported database

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

On Windows PowerShell, create the SQLite database file:

```powershell
New-Item -ItemType File -Force database/database.sqlite
```

Then migrate:

```bash
php artisan migrate
```

Set these required environment variables before seeding:

```dotenv
ADMIN_NAME=FOUcreative
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=use-a-long-random-password
```

Then seed the demo/admin data:

```bash
php artisan db:seed
```

Do not use the example admin password or demo credentials in a public deployment.

## Development

Run Laravel and Vite:

```bash
php artisan serve
npm run dev
```

Production frontend assets:

```bash
npm run build
```

## Tests

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

## Main Features

- Admin authentication
- Client status and profile management
- Nine-module onboarding forms with autosave
- KBLI, credential, knowledge base, and technical checklist management
- PDF onboarding document export
- Token-protected public onboarding links

## Routes

- `/login` - admin login
- `/admin` - admin dashboard
- `/admin/clients/{client}` - client detail and management
- `/onboarding/{token}` - token-protected client onboarding

## Security

Never commit `.env`, real passwords, API keys, credential values, or uploaded client documents.
Use HTTPS and a persistent storage disk for uploaded files in production.
