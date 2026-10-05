# Eloom LMS CRICOS

A learning and student management system for Australian Registered Training Organisations (RTOs) and VET providers. It covers the student lifecycle from enquiry and offer letter through enrolment, fees, classes, assessments, attendance and certification, with separate portals for admins, trainers, students, agents and agent branch users.

Built with Laravel 8 and [nwidart/laravel-modules](https://github.com/nWidart/laravel-modules); each feature area lives in its own module under `Modules/`.

## Features

| Area | Modules |
| --- | --- |
| Organisation setup | `Company`, `Setting`, `User` (admin users and roles), `Country`, `Address`, `Identifier` |
| Courses and intakes | `Course`, `Intake`, `Classroom`, `Resource`, `University`, `Credit` |
| Students | `Student`, `Document`, `Condition`, `OfferStatus`, `OfferTemplate`, `CertificateTemplate`, `Template` |
| Teaching and assessment | `Trainer`, `Assignment`, `Attendance`, `OnlineClass` (Zoom) |
| Recruitment | `Agent`, `AgentBranchUser`, `CRM` |
| Payments | `Payment` (Stripe), student fees and instalments |
| Communication | `Email`, `Notification` (Firebase push), `Chat` (Socket.io), `Ticket` |
| Reporting and compliance | `Report`, `Log`, AVETMISS/NAT export (in `Setting`) |
| Mobile/API | `Api` (Laravel Passport, OpenAPI docs at `/api/documentation`) |

## Requirements

- PHP 8.2, 8.3 or 8.4, with the `curl`, `gd`, `fileinfo`, `openssl`, `sodium`, `xml` and `zip` extensions
- Composer 2
- MySQL 5.7+ or MariaDB 10.3+
- Node.js 16+ and npm (only to rebuild front-end assets)
- Optional: a Socket.io server for real-time chat, a Firebase project for push notifications, Stripe and Zoom accounts

## Installation

```bash
git clone https://github.com/eloompty/Eloom-LMS-CRICOS.git
cd Eloom-LMS-CRICOS

composer install
cp .env.example .env
php artisan key:generate
```

Create a database, set the `DB_*` values in `.env`, then run:

```bash
php artisan migrate --seed
php artisan passport:install
php artisan storage:link
php artisan l5-swagger:generate
php artisan serve
```

### First run

Open `http://localhost:8000/admin`. With no users in the database, you'll see a setup form that creates the first **super admin** account and your organisation (company details, RTO and CRICOS numbers, and first delivery site). Registration closes as soon as one user exists.

Staff and learners then sign in at:

| Portal | URL |
| --- | --- |
| Admin | `/admin` |
| Trainer | `/trainer/login` |
| Student | `/student/login` |
| Agent | `/agent/login` |
| Agent branch user | `/branch-user/login` |

## Configuration

All settings are read from `.env`; see [`.env.example`](.env.example) for the full list. Many can also be changed later in **Admin > Settings**.

| Feature | Variables |
| --- | --- |
| Mail | `MAIL_*` (or per-organisation SMTP in Admin > Settings > Email) |
| Stripe payments | `STRIPE_KEY`, `STRIPE_SECRET` |
| Zoom online classes | `ZOOM_API_URL`, `ZOOM_API_KEY`, `ZOOM_API_SECRET`, `ZOOM_API_JWT`, `ZOOM_JOIN_URL` |
| Push notifications | `FIREBASE_*`, `FIREBASE_VAPID_KEY`, `SERVER_API_KEY` |
| Real-time chat | `SOCKET_SERVER_URL` (also exposed to the front end as `MIX_SOCKET_SERVER_URL`) |
| Word template import | `DOCUMENT_CONVERTER_URL`: a service that accepts `POST /convert` and returns HTML for an uploaded `.docx` |

`ZOOM_JOIN_URL` is the base that meeting links are built from. Zoom accounts are spread over
numbered clusters, so set it to the one your account uses — for example
`https://us06web.zoom.us/j/`.

Microsoft Teams classes are configured in **Admin > Settings**, not in `.env`.

Every one of these is read through `config/`, so `php artisan config:cache` is safe to run in
production.

Firebase settings from `.env` are also used by the messaging service worker, which the app serves at `/firebase-messaging-sw.js`.

## Front-end assets

Pre-built assets are committed under `public/`. To rebuild after changing `resources/` or module JavaScript:

```bash
npm install
npm run dev     # or: npm run prod
```

## Running tests

```bash
php artisan test
```

The same suite runs on every pull request through [GitHub Actions](.github/workflows/ci.yml),
against PHP 8.2 and MySQL 8.

## Project status

This is a production codebase released as open source. Known limitations:

- It runs on **Laravel 8**, which no longer gets security fixes. Upgrading to a supported Laravel version is the top roadmap item.
- `composer audit` still reports advisories in `laravel/framework`, `dompdf/dompdf` and `firebase/php-jwt`. Fixed versions of these need Laravel 9 or later, so they'll be resolved by the Laravel upgrade.
- File uploads are stored under `public/images/`. Types are checked on upload. On nginx, also block script execution in that folder (see [SECURITY.md](SECURITY.md#uploaded-files)).
- Test coverage is thin: the suite is the Laravel starter tests. Contributions here are especially welcome.
- Access control is enforced in controller constructors (`$this->middleware(...)`) and by `checkRole()`, not by route middleware. Any new controller has to declare its own guard.

See [CHANGELOG.md](CHANGELOG.md) for what changed in each release.

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) and our [Code of Conduct](CODE_OF_CONDUCT.md).

## Security

Please don't report security issues in public GitHub issues. See [SECURITY.md](SECURITY.md).

## Credits

Built by [Eloom Pty Ltd](https://eloom.com.au). This project bundles third-party assets, each under its own licence, including the [AdminLTE](https://adminlte.io) theme (MIT) and its plugins in `public/themes/AdminLTE`.

## License

Released under the [MIT License](LICENSE). Copyright (c) 2026 Eloom Pty Ltd.
