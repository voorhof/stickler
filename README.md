# Stickler

A Laravel 13 starter kit for applications using Filament 5 and e-commerce building blocks.

Ideal for content-focused websites and bespoke web shops, with a focus on support for the Dutch language and payments for Belgium and the Netherlands via Mollie.  

[![🚀 Stickler tests](https://github.com/voorhof/stickler/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/voorhof/stickler/actions/workflows/tests.yml)
[![Plumb](https://plumbphp.dev/badges/voorhof/stickler/composite.svg)](https://plumbphp.dev/voorhof/stickler)
[![Packagist](https://img.shields.io/packagist/v/voorhof/stickler.svg)](https://packagist.org/packages/voorhof/stickler)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://opensource.org/licenses/MIT)

A lightweight version of this starter kit, without the e-commerce building blocks is also available at:  
[https://github.com/voorhof/stickler-light](https://github.com/voorhof/stickler-light)  

## Features

Stickler is a production-ready Laravel starter kit for building content-driven websites and bespoke web applications. It combines a well-designed front-end framework with a powerful Filament CMS, enabling a new project to get off the ground quickly whilst all the essential components are already in place.

- **Filament 5 admin panel** — user management, navigation, notifications, soft-delete workflows, reusable form schemas and table definitions, resizable columns, and business-hours management.
- **E-commerce building blocks** — products with variants (option types, options, SKU, stock and price per variant), categories, orders, a public cart with navbar badge, real-time stock validation, concurrency-safe inventory reservations, and a complete hosted checkout flow powered by Mollie payments.
- **User authentication** — registration, login, password reset, email verification, protected account dashboard, and automatic guest cart merge on login.
- **Content management** — posts and pages with slugs, tags, sorting, localized content, rich text editing, and a custom source-code editor tool.
- **Media management** — powered by Spatie Media Library, including uploads, image conversions, responsive media support, Filament integration, and scheduled orphaned media cleanup.
- **Roles & permissions** — user access control, panel access checks, administrative lockout protection, and self-deletion fail-safes.
- **Page view analytics** — cookieless server-side tracking with bot filtering, daily hashed visitor IDs, dashboard widgets, and a view-only admin resource.
- **Activity logging** — model event tracking with attribute change diffs, causer support, and configurable log options via Spatie Laravel Activitylog.
- **Response caching** — production-ready HTTP response caching with dynamic cache headers to prevent CDN cache poisoning for authenticated or cart-bearing visitors.
- **Security & abuse protection** — form honeypots, rate limiting on sensitive endpoints, webhook signature verification, sanitized rich content rendering, and restricted access to admin and debugging tools.
- **Operational resilience** — Telescope for non-production debugging, application health checks, database and file backups via Spatie Laravel Backup, and scheduled task management.
- **Website essentials** — HTML head management via Laravel Head, model-driven sitemap generation, robots.txt support, opening hours, and cookie consent.
- **Developer experience** — Laravel Boost AI skills, Vite and Sass asset tooling, Pest tests, Laravel Pint code formatting, and comprehensive documentation.

## Documentation

| File | Description |
|------|-------------|
| [STICKLER.md](STICKLER.md) | Starter kit overview, project structure, installation, authentication, payment and checkout, and the production deployment checklist. |
| [FILAMENT.md](FILAMENT.md) | Filament admin panel implementation guide, including resources, plugins, store management, and page views. |
| [COMPOSER_PACKAGES.md](COMPOSER_PACKAGES.md) | Composer package dependencies and their roles in the application. |
| [NODE_MODULES.md](NODE_MODULES.md) | Node.js package dependencies for frontend asset building. |

## Quick Install

```bash
laravel new my-app --using=voorhof/stickler:v1.9.1 --phpunit --no-boost
```

Configure `.env` with your database, mail driver, and application settings. For detailed installation steps and production deployment, see [STICKLER.md](STICKLER.md).

## Security Vulnerabilities

If you discover a security vulnerability within Stickler, please send an e-mail to David Carton via [david@notrac.be](mailto:david@notrac.be).
All security vulnerabilities will be promptly addressed.

## License

Stickler is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
