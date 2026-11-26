# Agent Guide

## Project

Laravel 12 application for Fou Creative client onboarding and administration.

- Backend: PHP 8.2+, Laravel 12, Eloquent
- Frontend: Blade, Tailwind CSS 4, Vite 7, vanilla JavaScript
- PDF: `barryvdh/laravel-dompdf`
- Tests: PHPUnit 11
- Main domains: admin client management and public/token-based onboarding

## Before Changing Code

- Read the relevant route, controller, model, Blade view, and migration before editing.
- Trace feature behavior across request -> validation -> model -> view.
- Preserve existing Indonesian labels and status values unless the request explicitly changes them.
- Keep changes scoped to the request; do not reformat unrelated files.
- Never expose credential values, onboarding tokens, session data, or `.env` secrets.

## Architecture

- Routes live in `routes/web.php`.
- HTTP logic lives in `app/Http/Controllers/`.
- Onboarding token verification is handled by `VerifyOnboardingToken`.
- Database models live in `app/Models/`.
- Blade templates live in `resources/views/`.
- Frontend entry points are `resources/js/app.js` and `resources/css/app.css`.
- Database migrations and seeders live in `database/migrations/` and `database/seeders/`.

The client onboarding flow uses nine modules:

1. Profil perusahaan
2. Legalitas
3. Kontak dan aset digital
4. Kredensial
5. Billing
6. Aset
7. Kampanye
8. Ekosistem
9. Portofolio

Keep module numbering, route names, and review item names aligned. Review items use the `Portal Modul <n> Review` naming convention.

## Conventions

- Use Laravel resource naming and route names for controller actions.
- Validate all external input in controllers with `$request->validate(...)`.
- Protect mutations from authorization and CSRF issues; admin routes require authentication.
- Use Eloquent relationships instead of ad hoc joins.
- Preserve JSON responses for onboarding autosave endpoints.
- Store uploaded client files through Laravel Storage and keep file metadata in the related onboarding item.
- Use model casts for arrays, booleans, and dates.
- Follow existing PHP and Blade formatting; run Laravel Pint for touched PHP files.

## Database Changes

- Create a migration for every schema change.
- Never edit an existing migration after it has been applied to shared environments.
- Add or update factories and seeders when test or setup behavior depends on the change.
- Check foreign key and relationship behavior when changing client-related tables.

## Frontend Changes

- Use the existing Blade layout and Tailwind/Vite setup.
- Preserve responsive behavior and existing form field names.
- For autosave behavior, keep JSON success/error payloads compatible.
- Run `npm run build` after changing JavaScript or CSS entry files.

## Validation and Tests

Run the smallest relevant test set first:

```bash
php artisan test
php artisan test tests/Feature
vendor/bin/pint --test
npm run build
```

For a focused test file or test name:

```bash
php artisan test tests/Feature/ExampleTest.php
php artisan test --filter=TestName
```

When changing behavior, add a focused feature test that fails without the change. Keep tests independent of a developer's real database and credentials.

## Security and Sensitive Data

- Do not commit `.env`, API keys, credentials, uploaded documents, or database dumps.
- Be cautious with credential fields, file uploads, onboarding links, and admin status transitions.
- Treat onboarding token routes as access-controlled and validate the resolved client.
- Validate uploaded file types and sizes using existing Laravel validation patterns.

## Common Commands

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run dev
php artisan serve
composer test
npm run build
```

## Database and Environment

- Create a new migration for every schema change; never edit migrations already applied to shared environments.
- Run `php artisan migrate` after migration changes.
- Database seeders require `ADMIN_PASSWORD`; `ADMIN_NAME` and `ADMIN_EMAIL` are optional.
- Never place real credentials or secrets in source code or seeders.
