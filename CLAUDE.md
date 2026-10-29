# Wunschbox

Christmas wish box: guests submit wishes for Sonia or Tanja, admins manage them and keep a public wishlist. UI texts are German.

## Stack

Laravel 13 on PHP 8.4, Pest 5, Vite 8 with Tailwind 4 and Alpine 3, Intervention Image 4 (GD). SQLite locally. Node ≥ 22.12 (`.nvmrc` = 24).

## Commands

- Tests: `php artisan test` (needs a built `public/build/manifest.json`, otherwise view tests fail with a Vite manifest error)
- Build assets: `nvm use && npm run build`
- Dev (server, queue, logs, Vite): `nvm use && composer dev`
- Fresh local DB with the admin seed user: `php artisan migrate:fresh --seed`

## Areas

- **Wunschbox** (`/`, `WishboxController`): public form, guest wishes are stored with `is_public = false`.
- **Wunschliste** (`/wishlist`, `WishlistController`): Tanja's public wish note, a numbered letter to Santa sorted by `sortnr`. Everything not on it is visible only to admins.
- **Admin** (`/admin`, `AdminController`): guest wishes with status filters, quick edits (status, sort number, package number), own public wishes, profile at `/profile`.

## Rules that are easy to break

- **Admins only.** There is no registration. Login accepts only `is_admin` users (`LoginRequest`), and every admin, profile and password route uses the `admin` middleware (`EnsureUserIsAdmin`). Keep new authenticated routes behind `['auth', 'admin']`, since a remember-me cookie skips the login check.
- **The public Wunschliste is only ever Tanja's wish note** (`config('wishbox.owner')`). Query it with `Wish::onWishNote()` (public and receiver = owner), never with `is_public` alone; guests get a 404 for anything else. Admin-added wishes always get the owner as receiver, wishes on the note keep her when edited, and only the owner's wishes can be toggled public (`Wish::canTogglePublic()`). No receiver labels in the public views.
- **Receivers** come from `Wish::RECEIVERS`. `StoreWishRequest` validates against it, `AdminStoreWishRequest` also allows the current receiver of an existing wish. The wish form builds its receiver radios from the same list.
- **`sortnr`** is an unsigned integer column cast to `int`. Order lists with `Wish::sorted()`.
- **Statuses** are the `Wish::STATUS_*` constants; labels and badge colors live in `Wish::getStatuses()` and `status_color`.
- **Images** go through `ImageService`: original max 1920×1080, thumbnail 400×300 stored as `image_thumbnail`. Build thumbnail paths only with `ImageService::getThumbnailPath()`. Migrations must not depend on app services.
- **Sessions** use JSON serialization (`config/session.php`); don't store PHP objects in the session.

## Conventions

- Admin pages use `<x-admin-layout>` (`title`, optional `heading`, slots `subtitle`, `actions`, `scripts`); public pages use `<x-public-layout>` (`title`, slots `header`, `scripts`).
- Design "Wunschzettel": tokens in `resources/css/app.css` (`pine`, `berry`, `seal`, `gold`, `paper`, `line`, `ink`), Caveat (`font-hand`) for headings and wish titles, Nunito for the rest, both self-hosted via Fontsource. Header deco is `<x-fairy-lights>`. Alpine comes from npm (`resources/js/app.js`), not a CDN.
- Shared wish form: `<x-wish-form>`.
- Validation messages shown to users are German; pass custom messages where Laravel's English defaults would show.
- Every bugfix or feature gets a Pest feature test in `tests/Feature/`; use `User::factory()->admin()` for admin users and `Wish::factory()` for wishes.
- Commits follow `type: Message` (`feat`, `fix`, `chore`, `refactor`), split by topic.

## Local setup

- Served by Laravel Herd at `https://wunschbox2025.test`; keep `APP_URL` on https.
- Seed admin: `tanja@test.com` with the default password from `UserFactory`.
