# Contributing

Thanks for helping to improve Eloom LMS CRICOS. This guide explains how to propose changes.

## Before you start

- For bugs, search existing issues first. If there isn't one, open an issue with steps to reproduce, what you expected and what happened.
- For new features or larger changes, open an issue to discuss the idea before you write code. That saves wasted effort if the change doesn't fit the project.
- For security issues, follow [SECURITY.md](SECURITY.md) instead of opening an issue.

## Development setup

Follow the installation steps in the [README](README.md). Use a local database and never commit a real `.env` file or credentials.

## Making a change

1. Fork the repository and create a branch from `main` (for example `fix/attendance-export` or `feature/course-prerequisites`).
2. Keep the change focused. One pull request should do one thing.
3. Follow the existing code style. Each feature lives in its own module under `Modules/`, so put new code in the module it belongs to.
4. Add or update tests where it's practical, and run `php artisan test`. CI runs the same suite on your pull request.
5. Update the README, `.env.example` and `CHANGELOG.md` if you add configuration or change behaviour.
6. Open a pull request that describes what changed and why, and link the related issue.

## Two things that are easy to get wrong

**Authentication.** Routes are not protected by middleware. Every controller declares its own
guard in its constructor, and permissions are checked per action:

```php
public function __construct()
{
    $this->middleware('auth:user'); // or auth:student, auth:trainer, auth:agent
}

public function store(Request $request)
{
    if (checkRole('student_intake_course_fee', 'add') != true) {
        return abort(404);
    }
    // ...
}
```

A new controller with no constructor is reachable by anyone. Please don't add one.

**Configuration.** Read settings with `config('services.…')`, never `env()` outside
`config/`. `env()` returns `null` once `php artisan config:cache` has run in production.

## Commit messages

Write short, imperative subject lines, for example "Fix fee instalment rounding" or "Add CSV export to attendance report".

## Licence

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).

## Questions

Open a GitHub discussion or issue, or email admin@eloom.com.au.
