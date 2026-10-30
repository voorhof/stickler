# Stickler

A Laravel application starter kit.

[![🚀 Test Laravel Application](https://github.com/voorhof/stickler/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/voorhof/stickler/actions/workflows/tests.yml)
[![Packagist](https://img.shields.io/packagist/v/voorhof/stickler.svg)](https://packagist.org/packages/voorhof/stickler)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## About Stickler

Stickler is a production-minded Laravel starter kit for building content-driven websites and custom web applications. It combines a polished public-facing foundation with a powerful Filament CMS, so a new project can start with the essentials already implemented instead of spending its first weeks assembling infrastructure.

Out of the box, Stickler includes:

- **A complete Laravel foundation** with environment-based configuration, database migrations, seed data, queues, scheduled tasks, and a structure that is ready to customize.
- **A Filament 5 admin panel** with user management, navigation, notifications, soft-delete workflows, reusable form schemas and table definitions, resizable columns, and business-hours management. See the [Filament implementation guide](FILAMENT.md) for details.
- **Content management building blocks** for posts and other content, including slugs, tags, sorting, localized content, rich text editing, and a custom rich-editor source-code tool.
- **E-commerce building blocks** with products and product variants (option types, options, SKU, stock and price per variant), categories, orders with order and payment status, and a public cart with a navbar badge, stock validation, and an expiry cleanup. See the [Store Management section](FILAMENT.md#store-management) of the Filament guide for the admin side.
- **Media management** powered by Spatie Media Library, including uploads, image conversions, responsive media support, and integration with Filament.
- **User access and administration** with roles and permissions, panel access checks, admin configuration through `.env`, and optional two-factor authentication support.
- **Website essentials** such as HTML head management, sitemap generation, robots.txt support, opening hours, cookie consent, and a Bootstrap-based public frontend.
- **Security and abuse protection** with form honeypots, rate limiting, sanitized rich content rendering, and restricted access to administration and debugging tools.
- **Operational visibility and resilience** with Telescope for non-production debugging, HTTP and activity logging, cookieless server-side page view analytics, application health checks, database and file backups, and response caching for production workloads. Telescope is not booted in production. See the [Page Views section](FILAMENT.md#page-views) of the Filament guide for details.
- **Developer experience** with Laravel Boost skills, Vite and Sass asset tooling, Pest tests, Laravel Pint code style checks, and documentation for the installed Composer and Node dependencies.

The result is a strong baseline for launching a website quickly while keeping the codebase understandable and flexible. Replace the branding, content model, frontend views, and business rules with your own requirements, while retaining the reusable CMS, administration, and operational features that every project needs.

[Bootstrap](https://getbootstrap.com/) frontend toolkit is used for styling the public views.

## Project Structure and Customization

Stickler separates the public website, the administration area, and the reusable application services so the starter kit can be adapted without first restructuring the project:

- `app/Models` contains the application's domain models. Extend these models when adding project-specific content, relationships, media collections, activity tracking, or permissions.
- `app/Http/Controllers/Public` and `resources/views/public` contain the public website. Public controllers prepare page data, while Blade views are responsible for rendering the Bootstrap-based frontend.
- `app/Filament` contains the admin resources, pages, schemas, tables, imports, filters, and project-specific Filament extensions. Keep resource forms and tables in their dedicated `Schemas` and `Tables` classes instead of growing the main resource class.
- `app/Providers` contains application-wide configuration. The Filament panel and shared UI behavior are configured in the Filament providers, while application services and view behavior belong in the appropriate provider rather than in individual views.
- `app/Actions`, `app/Observers`, `app/Policies`, and `app/Traits` contain reusable business operations and cross-cutting conventions already used by the application. Prefer extending these patterns over duplicating creator/updater, media, activity-log, observer, or authorization logic.
- `database/migrations`, `database/seeders`, and `database/factories` describe the project's data model and local baseline data. Seeders are intended to make a fresh installation usable, so update them when required configuration or starter content changes.
- `resources/scss` and `resources/js` contain the frontend source files. Customize the Bootstrap variables and project styles there; do not edit generated files in `public` as the source of truth.
- `lang` contains the project's English and Dutch translations, including translations published by the admin integrations. Add application copy to the project language files instead of hard-coding it in views.
- `tests` mirrors the application areas and is part of the customization workflow. New domain behavior, public pages, permissions, and admin workflows should receive focused coverage alongside the implementation.

### Starter Kit Customization Checklist

When starting a project from Stickler, replace the sample identity and content deliberately rather than treating the repository as a blank Laravel installation:

1. Set the application name, URL, locale, administrator credentials, and feature flags in `.env`.
2. Replace the artwork and branding in `public` and update references to those assets in the Blade layouts and views.
3. Review the seeded users, roles, permissions, opening-hours record, and sample content (products, orders and carts) before using the project outside local development.
4. Adapt the public routes, controllers, views, models, and Filament resources to the new project's content model and navigation.
5. Keep generated or published vendor assets in sync when updating the related package; project-owned source files remain the place for custom behavior.
6. Regenerate `llms.txt` so it describes the new project's pages and content instead of the starter kit's defaults.

The dedicated [Filament implementation guide](FILAMENT.md), [Composer package guide](COMPOSER_PACKAGES.md), and [Node modules guide](NODE_MODULES.md) document package-specific behavior; this section intentionally focuses on how to work with Stickler itself.

## Installing Stickler

### Laravel Installer

The easiest way to install this starter kit is using the [Laravel Installer](https://github.com/laravel/installer).
This will set up the project automatically.

```bash
laravel new my-app --using=voorhof/stickler
```

After installation, update the variables, such as `APP_NAME` and `APP_URL`, in the `.env` file so that they match your environment.

You can also set `RESPONSE_CACHE_ENABLED` to true (not required for development, but useful for production)

```dotenv
APP_NAME=Stickler
APP_URL=https://stickler.test

RESPONSE_CACHE_ENABLED=true
```

When response caching is enabled, pages are not cached for visitors with a `cart_token` cookie or a logged-in account. Their navbar cart badge is personal, so a cached page would show the wrong count. After upgrading an existing installation, run `php artisan responsecache:clear` once to remove pages cached before this rule existed.

You can log in to the Filament admin dashboard via `/admin` using the login details defined in .env:

- email: admin@example.com
- password: password

### Custom installation

You may also install Stickler manually by cloning the repository and running the following commands:

```bash
git clone https://github.com/voorhof/stickler.git my-app
cd my-app
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

Make sure you have all this covered:
- composer install
- npm install
- storage symlink
- database creation
- migration & test data seeding
- PhpStorm settings (or other editor)
- update art in public folder (and references to the logo's inside blade views)
- re-generate llms.txt to fit your project

## Filament CMS

A separate documentation file is available for the Filament CMS implementation:  
[FILAMENT.md](FILAMENT.md)

## Composer Package Dependencies

A separate documentation file is available for the Composer package dependencies:
[COMPOSER_PACKAGES.md](COMPOSER_PACKAGES.md)

Highlighted packages:

- **Laravel Boost** - AI skills for the Junie assistant and other agents
- **Laravel Telescope** - Debugging assistant for requests, queries, and jobs
- **Laravel Head** - Manages HTML head meta tags
- **Spatie Sluggable** - Generates unique slugs for models
- **Spatie Permission** - Roles and permissions
- **Spatie Media Library** - Associates files with Eloquent models
- **Spatie Tags** - Tagging for Eloquent models
- **Spatie Eloquent Sortable** - Sortable Eloquent models
- **Spatie Settings** - Application settings storage
- **Spatie Activity Log** - Logs model events and activities
- **Spatie Sitemap** - Sitemap generation
- **Spatie Health** - Application health checks
- **Spatie Backup** - Database and file backups
- **Spatie Opening Hours** - Opening hours handling
- **Filament Business Hours Plugin** - Business hours for the Filament admin panel
- **Spatie Honeypot** - Spam protection for forms
- **Spatie Cookie Consent** - Cookie consent banner
- **Spatie Response Cache** - HTTP response caching
- **Spatie HTTP Logger** - HTTP request logging
- **OPcodes Log Viewer** - Log viewer for the application

## Node Modules Dependencies

A separate documentation file is available for the Node Modules dependencies:
[NODE_MODULES.md](NODE_MODULES.md)

Highlighted packages:

- **Bootstrap** - CSS framework for layout and UI components
- **Popper** (`@popperjs/core`) - Positioning engine for Bootstrap's Dropdown
- **Bootstrap Icons** - Icon set from the Bootstrap team
- **Sass** - SCSS compiler
- **Vite** - Build tool and development server
- **Laravel Vite Plugin** - Connects Vite to Laravel
- **Fontaine** - Fallback font metrics generator
- **Rollup Plugin Replace** - Replaces `data-bs` with `data` in build output
- **Prettier** - Formats frontend source files
- **Prettier Blade Plugin** - Adds Blade template formatting support
- **Prettier Tailwind CSS Plugin** - Sorts Tailwind utility classes when applicable

## Testing

To test the application and run all tests included in the `tests` directory, run the following command:

```bash
php artisan test --compact --parallel
```

You can also execute Pest directly in the terminal:

```bash
./vendor/bin/pest
```

## Code Style

Laravel Pint is used to check the code style, run the following command before committing:

```bash
./vendor/bin/pint
```

## Security Vulnerabilities

If you discover a security vulnerability within Stickler, please send an e-mail to David Carton via [david@notrac.be](mailto:david@notrac.be).
All security vulnerabilities will be promptly addressed.

## License

Stickler is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
