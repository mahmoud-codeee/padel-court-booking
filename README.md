# Padel Court Booking Platform

An online booking platform for a padel venue: a public, no-login client booking flow and an authenticated admin dashboard, built on a Laravel API with a separate React SPA frontend.

## Live Demo

- **Client booking flow**: https://frontend-nine-blond-5v3rf0171e.vercel.app
- **Admin dashboard**: https://frontend-nine-blond-5v3rf0171e.vercel.app/admin/login
  - Email: `admin@padel.local`
  - Password: `PadelDemo_2026!Xk`
- **Backend API**: to see it directly (not just through the apps above), open [`/api/pricing`](https://backend-production-941c.up.railway.app/api/pricing) for a live JSON response — the bare API host has no route of its own and 404s if opened by itself, so don't click that

Hosted on Railway (Laravel API + MySQL) and Vercel (React SPA). Online payment via Thawani isn't testable on the live demo (no real sandbox credentials configured — see [Setup §3](#3-thawani-online-payments)); "Pay on arrival" works end-to-end.

## Technologies Used

**Backend**
- Laravel 13 (PHP 8.4), MySQL 8
- Laravel Sanctum (token-based admin authentication)
- PHPUnit feature/unit tests

**Frontend**
- React 19 + TypeScript, Vite
- Tailwind CSS v4 + shadcn/ui (Radix primitives)
- TanStack Query (server state), Zustand (booking cart / admin auth state)
- React Router, React Hook Form + Zod

**Payments**
- Thawani payment gateway (sandbox), plus a pay-on-arrival (cash) option

## Project Structure

```
/backend    Laravel API
/frontend   React SPA (Vite)
```

## Prerequisites

- PHP 8.4+ with the `openssl`, `pdo_mysql`, `mbstring`, `curl`, `fileinfo`, `zip` extensions
- Composer
- MySQL 8 (or compatible) reachable from the backend
- Node.js 18+ and npm

## Setup

### 1. Backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Edit `.env` and point `DB_*` at your MySQL server, then create the database (e.g. `CREATE DATABASE padel_booking;`) and run:

```bash
php artisan migrate --seed
php artisan serve
```

This seeds:
- One admin account (see **Admin Login** below)
- 3 example courts, each open 08:00–23:00 every day
- A base price of 5.000 OMR/hour with example discount tiers (1h → 5.00, 2h → 4.50, 3h+ → 4.00 per hour)

The API is now available at `http://127.0.0.1:8000/api`.

**Booking holds**: online-payment bookings place a 10-minute hold on their slots while awaiting payment. For holds to expire and free up slots automatically, run the scheduler in a second terminal:

```bash
php artisan schedule:work
```

(In production this would be a single cron entry running `php artisan schedule:run` every minute instead.)

### 2. Frontend

```bash
cd frontend
npm install
npm run dev
```

`frontend/.env` already points `VITE_API_URL` at `http://127.0.0.1:8000/api` (the default `php artisan serve` address) — only change it, or add a git-ignored `.env.local` override, if your backend runs elsewhere.

The app is now available at `http://localhost:5173` — the client booking flow at `/`, the admin dashboard at `/admin/login`.

### 3. Thawani (online payments)

The backend ships pointed at Thawani's UAT sandbox (`THAWANI_BASE_URL` in `.env`), but `THAWANI_SECRET_KEY` / `THAWANI_PUBLISHABLE_KEY` are left blank — get sandbox keys from [Thawani's developer docs](https://thawani-ecommerce-technologies.stoplight.io/docs/thawani-api-commerce-e-thawani-api/5534c91789a48) and set them to actually complete an online payment. Without them, choosing "Pay online" at checkout fails gracefully (the booking's held slots are released and the customer is told to try again or pay on arrival) — "Pay on arrival" works fully out of the box either way.

## Admin Login

```
URL:   http://localhost:5173/admin/login
Email: admin@padel.local
```

Created via the database seeder — see `.env.example`. No password is committed to this repo: set `ADMIN_PASSWORD` in your own `backend/.env` before seeding, or leave it blank and `php artisan migrate --seed` will generate one and print it to the terminal once (save it immediately — it isn't shown again, and re-running the seeder afterwards won't reset it). `ADMIN_EMAIL` is also configurable there if you don't want the default.

## Running Tests

```bash
cd backend
php artisan test
```

Tests run against a **separate MySQL database** (not the dev one), because the booking-concurrency and court-assignment logic relies on real row-level locking and native `ENUM`/`JSON` columns that SQLite can't faithfully emulate. Create it once (defaults in `backend/phpunit.xml` assume `root` with no password on `127.0.0.1:3306` — adjust there if your local MySQL differs):

```sql
CREATE DATABASE padel_booking_test;
```

Coverage includes: court auto-assignment (same-court-for-contiguous-hours preference, fallback per-hour assignment, conflict detection), the full booking creation flow (pricing tiers, cash vs. online status/hold behavior, working-hours/closure/exhaustion rejection, slot release), the public API (past-date validation, 409 conflict handling, and — importantly — that court identity never appears in any client-facing response), and admin authentication.

## Key Design Decisions

- **Court identity is never exposed to clients.** Because of this, pricing is intentionally **global** (one base price + hour-based discount tiers), not per-court — the client always sees a deterministic price before a court is randomly assigned at booking confirmation.
- **Availability is aggregated across courts.** A time slot stays bookable as long as at least one non-closed court is free at that hour; the specific court is assigned automatically and randomly on confirmation, preferring to keep a whole contiguous multi-hour booking on the same court.
- **Concurrency safety**: booking creation runs inside a DB transaction that locks all active courts (`SELECT ... FOR UPDATE`) before re-checking availability and inserting slots, backed by a `UNIQUE(court_id, slot_date, slot_hour)` database constraint as a last line of defense — two people can never be sold the same court-hour.
- **Cash bookings** reserve their slots immediately (status `confirmed`, payment `unpaid`, settled at the venue). **Online bookings** hold their slots for 10 minutes while payment completes; the hold is released automatically if payment isn't confirmed in time.
- Currency is **OMR** (matching the Thawani gateway) rather than the SAR figures used illustratively in the original spec.
- Admin can close a single court, several courts, or the entire venue for a date (optionally a partial time range instead of the full day).
