# Contributing to Stickler

Thank you for considering a contribution to Stickler. Bug reports, feature ideas, documentation fixes, and pull requests are all welcome.

## Before You Start

- Search the [existing issues](https://github.com/voorhof/stickler/issues) to avoid duplicates.
- For larger changes, open an issue first so we can agree on the approach.
- By participating, you agree to abide by the [Code of Conduct](CODE_OF_CONDUCT.md).

## Development Setup

```bash
git clone https://github.com/voorhof/stickler.git
cd stickler
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

## Making Changes

1. Fork the repository and create a branch from `main`.
2. Keep each pull request focused on a single change.
3. Follow the existing code style and conventions. Check sibling files before creating new ones.
4. Add or update tests for behaviour changes.
5. Update `README.md`, `FILAMENT.md`, or `CHANGELOG.md` when relevant.

## Quality Checks

Run these before opening a pull request:

```bash
./vendor/bin/pint
php artisan test --compact --parallel
```

## Pull Requests

- Use a clear title and describe what changed and why.
- Link related issues, for example `Closes #12`.
- Make sure all checks pass. Pull requests with failing tests or style issues will not be merged.

## Reporting Bugs

Use the bug report issue template and include steps to reproduce, expected behaviour, actual behaviour, and your environment (PHP, Laravel, and database versions).

## Questions

For usage questions, open a discussion or an issue rather than contacting maintainers directly.
