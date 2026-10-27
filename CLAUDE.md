# Wunschbox

Christmas wish box: guests submit wishes for Sonia or Tanja, admins manage them and keep a public wishlist. UI texts are German.

## Stack

Laravel 13 on PHP 8.4, Pest 5, Vite 8 with Tailwind 4, Intervention Image 4 (GD). SQLite locally. Node ≥ 22.12 (`.nvmrc` = 24).

## Commands

- Tests: `php artisan test` (needs a built `public/build/manifest.json`, otherwise view tests fail with a Vite manifest error)
- Build assets: `nvm use && npm run build`
- Dev (server, queue, logs, Vite): `nvm use && composer dev`
- Fresh local DB with the admin seed user: `php artisan migrate:fresh --seed`

## Areas

- **Wunschbox** (`/`, `WishboxController`): public form, guest wishes are stored with `is_public = false`.
- **Wunschliste** (`/wishlist`, `WishlistController`): public list of wishes with `is_public = true`, sorted by `sortnr`. Private wishes are visible only to admins.
- **Admin** (`/admin`, `AdminController`): guest wishes with status filters, quick edits (status, sort number, package number), own public wishes, profile at `/profile`.

## Rules that are easy to break

- **Admins only.** There is no registration. Login accepts only `is_admin` users (`LoginRequest`), and every admin, profile and password route uses the `admin` middleware (`EnsureUserIsAdmin`). Keep new authenticated routes behind `['auth', 'admin']`, since a remember-me cookie skips the login check.
- **Receivers** come from `Wish::RECEIVERS`. `StoreWishRequest` validates against it, `AdminStoreWishRequest` also allows the current receiver of an existing wish. The wish form builds its select from the same list.
- **`sortnr`** is an unsigned integer column cast to `int`. Order lists with `Wish::sorted()`.
- **Statuses** are the `Wish::STATUS_*` constants; labels and badge colors live in `Wish::getStatuses()` and `status_color`.
- **Images** go through `ImageService`: original max 1920×1080, thumbnail 400×300 stored as `image_thumbnail`. Build thumbnail paths only with `ImageService::getThumbnailPath()`. Migrations must not depend on app services.
- **Sessions** use JSON serialization (`config/session.php`); don't store PHP objects in the session.

## Conventions

- Admin pages use the `<x-admin-layout>` component (`title`, optional `heading`, slots `subtitle`, `actions`, `scripts`). The public pages don't share a layout yet.
- Shared wish form: `<x-wish-form>`.
- Validation messages shown to users are German; pass custom messages where Laravel's English defaults would show.
- Every bugfix or feature gets a Pest feature test in `tests/Feature/`; use `User::factory()->admin()` for admin users and `Wish::factory()` for wishes.
- Commits follow `type: Message` (`feat`, `fix`, `chore`, `refactor`), split by topic.

## Local setup

- Served by Laravel Herd at `https://wunschbox2025.test`; keep `APP_URL` on https.
- Seed admin: `tanja@test.com` with the default password from `UserFactory`.
