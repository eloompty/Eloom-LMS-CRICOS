# Changelog

All notable changes to this project are recorded here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project uses
[semantic versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] — 2026-10-05

First public release, extracted from a production codebase and relicensed under MIT.

### Added

- `LICENSE` (MIT), `README.md`, `SECURITY.md`, `CONTRIBUTING.md` and `CODE_OF_CONDUCT.md`
- GitHub issue forms, a pull request template, Dependabot config and a CI workflow that
  runs migrations and `php artisan test` against MySQL
- `public/images/.htaccess`, plus nginx guidance in `SECURITY.md`, to stop scripts running
  in the upload folder
- Upload type checking through `uploadFile()` and `checkUploadFile()` in `app/helpers.php`

### Changed

- Firebase, the FCM server key, Stripe, Zoom, the Socket.io chat server and the docx→HTML
  converter are all read from `.env` through `config/services.php` instead of being
  hardcoded or read with `env()` at runtime, so `php artisan config:cache` is now safe
- Offer letters, certificates and payment receipts take the organisation's name, addresses
  and bank details from the Company record rather than hardcoded values
- Admin-entered template names are sanitised before they are used as file names
- Dependencies updated to the newest versions compatible with Laravel 8
- Requires PHP 8.2, 8.3 or 8.4 (the locked dependencies no longer support 8.1); Composer is
  pinned to the PHP 8.2 platform so updates stay installable on 8.2

### Removed

- Unauthenticated debug routes: a file upload test endpoint, a remote "super admin" login
  and a public `phpinfo()` page
- `GET /admin/student/export`, an unauthenticated CSV export of every student's name, date
  of birth, passport number and citizenship
- `/api/meetings`, an unauthenticated CRUD interface onto the organisation's Zoom account.
  The application used to call it over HTTP against itself to schedule a class; it now calls
  the Zoom API directly through `App\Services\ZoomMeetingService`
- Unused scaffold controllers and sample templates left over from development

### Fixed

- `POST /admin/register` now only creates the first super admin, not one at any time
- `POST /admin/backup/restore` requires an authenticated admin with the
  `database_backup` permission, and no longer shells out to `mysql` with the database
  password on the command line
- The database backup download passes the password through the environment instead of the
  process list
- The Microsoft OAuth callback validates a `state` parameter and requires a signed-in user
- Student unit fees can no longer be created or edited without an admin session and the
  `student_intake_course_fee` permission, and the update no longer mass-assigns the request
- dompdf runs with PHP execution disabled, and student and agent fields are escaped before
  they are inserted into offer letter and certificate templates
- The first-run registration page no longer fails with a 500 when the state reference data
  has not been seeded

[Unreleased]: https://github.com/eloompty/Eloom-LMS-CRICOS/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/eloompty/Eloom-LMS-CRICOS/releases/tag/v1.0.0
