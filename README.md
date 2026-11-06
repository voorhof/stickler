# Stickler

A Laravel application starter kit.

[![🚀 Stickler tests](https://github.com/voorhof/stickler/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/voorhof/stickler/actions/workflows/tests.yml)
[![Plumb](https://plumbphp.dev/badges/voorhof/stickler/composite.svg)](https://plumbphp.dev/voorhof/stickler)
[![Packagist](https://img.shields.io/packagist/v/voorhof/stickler.svg)](https://packagist.org/packages/voorhof/stickler)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](https://opensource.org/licenses/MIT)

## About Stickler

Stickler is a production-minded Laravel starter kit for building content-driven websites and custom web applications. It combines a polished public-facing foundation with a powerful Filament CMS, so a new project can start with the essentials already implemented instead of spending its first weeks assembling infrastructure.

Out of the box, Stickler includes:

- **A complete Laravel foundation** with environment-based configuration, database migrations, seed data, queues, scheduled tasks, and a structure that is ready to customize.
- **A Filament 5 admin panel** with user management, navigation, notifications, soft-delete workflows, reusable form schemas and table definitions, resizable columns, and business-hours management. See the [Filament implementation guide](FILAMENT.md) for details.
- **Content management building blocks** for posts and other content, including slugs, tags, sorting, localized content, rich text editing, and a custom rich-editor source-code tool.
- **User authentication** with registration, login, password reset, email verification, and a protected account dashboard. Guest carts are automatically merged into the authenticated cart on login or registration. See the [User Authentication section](#user-authentication) for details.
- **E-commerce building blocks** with products and product variants (option types, options, SKU, stock and price per variant), categories, orders with order and payment status, a public cart with a navbar badge, stock validation and expiry cleanup, and a complete hosted checkout flow powered by Mollie payments. See the [Store Management section](FILAMENT.md#store-management) of the Filament guide and the [Payment and Checkout section](#payment-and-checkout).
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

### Before Going Live

When preparing a Stickler-based project for production deployment, complete the following checklist to ensure the application is secure, properly configured, and ready for real traffic:

- [ ] **Change all default credentials** — Update the seeded admin user passwords and review the `ADMIN_EMAIL` and `ADMIN_PASSWORD` values in `.env`.
- [ ] **Update application identity** — Set `APP_NAME`, `APP_URL`, and `APP_LOCALE` in `.env` to match your project.
- [ ] **Configure a real mail driver** — Replace the default `log` mail driver with a production-ready service (e.g., Mailgun, Postmark, SES) and update the `MAIL_` variables in `.env`.
- [ ] **Set up payment gateway** — Replace the test `MOLLIE_KEY` with a live API key and verify `PAYMENT_WEBHOOK_URL` points to your production domain.
- [ ] **Review and update seeded content** — Replace sample products, categories, posts, pages, and opening hours with your own content.
- [ ] **Disable debug mode** — Set `APP_DEBUG=false` and `APP_ENV=production` in `.env`.
- [ ] **Secure sessions** — Set `SESSION_SECURE_COOKIE=true` when serving over HTTPS.
- [ ] **Configure backups** — Verify `BACKUP_` settings in `.env` and run `php artisan backup:run` to confirm backups work.
- [ ] **Enable response caching** — Set `RESPONSE_CACHE_ENABLED=true` for production performance.
- [ ] **Configure queue worker** — Ensure a queue worker is running (e.g., via supervisor) for email delivery and webhook processing.
- [ ] **Set up monitoring** — Configure health checks and review Telescope is disabled in production (default behavior).
- [ ] **Update branding** — Replace the default logo and artwork in `public/` and update any hardcoded references in Blade views.

## Installing Stickler

### Laravel Installer

The easiest way to install this starter kit is using the [Laravel Installer](https://github.com/laravel/installer).
This will set up the project automatically.

```bash
laravel new my-app --using=voorhof/stickler:v1.8.3 --phpunit
```

> **Important:** Always specify a version tag (e.g., `v1.8.3`) when using the `--using` flag. Without a version, the Laravel installer will check out the development branch (`dev-main`) instead of the latest stable release.

The `--phpunit` flag is required because Stickler already includes Pest. Without it, the Laravel installer will attempt to install Pest again and run the `pest-plugin-drift` migration tool, which may modify the existing test files.

At the end of the installation, the Laravel installer may prompt you with interactive Laravel Boost questions. Boost is already pre-configured for Stickler via `boost.json`, so you can simply accept the defaults by pressing Enter on each prompt. If you prefer to skip these prompts entirely, use the `--no-boost` flag:

```bash
laravel new my-app --using=voorhof/stickler:v1.8.3 --phpunit --no-boost
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
git clone --branch v1.8.3 https://github.com/voorhof/stickler.git my-app
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

## User Authentication

Public user authentication allows customers to create an account, log in, manage their profile, and access a protected account dashboard. The authentication flow integrates with the cart system so guest carts are preserved when a user logs in or registers.

### Registration and login

- `RegisteredUserController` (`app/Http/Controllers/Auth`) handles user registration. The registration form includes a terms-of-service checkbox and is protected by a honeypot and rate limiting (3 attempts per minute).
- `AuthenticatedSessionController` handles login and logout. After a successful login, the session is regenerated for security.
- Routes are defined in `routes/auth.php` under the `guest` middleware group, so authenticated users are redirected away from these pages.
- The registration route uses the Dutch URL `/registreer` and the login route uses `/login`.

### Password management

- `PasswordResetLinkController` sends password reset emails. Non-existent emails are handled gracefully to prevent user enumeration.
- `NewPasswordController` processes the password reset link and stores the new password.
- `PasswordController` allows authenticated users to update their password from the account dashboard.
- `ConfirmablePasswordController` requires password confirmation before sensitive actions.

### Email verification

- `EmailVerificationNotificationController` resends the email verification link.
- `EmailVerificationPromptController` shows the verification prompt page.
- `VerifyEmailController` verifies the email using a signed, throttled verification link.
- The `verified` middleware is applied to the account dashboard route, so unverified users are redirected to the verification prompt.

### Account dashboard

- `AccountController` (`app/Http/Controllers/Account`) provides the protected account dashboard at `/account/dashboard`.
- The dashboard requires both the `auth` and `verified` middleware, ensuring only verified users can access it.
- The view is located at `resources/views/account/dashboard.blade.php`.

### Guest cart merge on login

When a guest user adds items to their cart and then logs in or registers, the `MergeGuestCart` middleware automatically merges the guest cart into the authenticated user's cart.

- `MergeGuestCart` (`app/Http/Middleware`) is applied only to the `POST /login` and `POST /registreer` routes, not globally, to avoid unnecessary overhead on every request.
- The middleware runs after the response is generated (`$next($request)`), so the user is already authenticated before the merge.
- If the user has a `cart_token` cookie pointing to a non-empty guest cart, the guest cart items are merged into the user's authenticated cart using `CartService::merge()`.
- After the merge, the `cart_token` cookie is updated to point to the user's authenticated cart, so subsequent requests use the correct cart.
- If the guest cart is empty, not a guest cart, or the user has no `cart_token` cookie, the middleware returns immediately without any database queries.

### Authentication views

All authentication views are located in `resources/views/auth/` and use the `<x-layouts.app>` layout with Bootstrap styling:

- `register.blade.php` — Registration form with name, email, password, terms checkbox, and login link.
- `login.blade.php` — Login form with email, password, remember me, forgot password link, and registration link.
- `forgot-password.blade.php` — Password reset request form.
- `reset-password.blade.php` — New password form (shown after clicking the reset link).
- `verify-email.blade.php` — Email verification prompt with resend link.
- `confirm-password.blade.php` — Password confirmation form.

## Payment and Checkout

Stickler includes a complete checkout and payment lifecycle integrating a database-backed cart, customer address collection, hosted payment gateway redirects, asynchronous webhook synchronization, and customer email notifications.

### Checkout Flow

1. **Cart Review (`GET /cart`)**: Customers review items, adjust quantities with real-time stock validation, or remove products. Product discounts and variant pricing rules are automatically applied.
2. **Order Confirmation (`GET /checkout`)**: Customers provide contact info (name, email, phone), shipping address, and optional billing address (with a "same as shipping" toggle) and order notes. For logged-in users, details are pre-filled automatically from their user profile.
3. **Order Placement (`POST /checkout`)**: Validates the form via `StoreOrderRequest`, creates an `Order` and associated `OrderItem` snapshots in a database transaction, clears the active cart, and delegates payment creation to `PaymentService`.
4. **Hosted Payment Redirect**: The customer is redirected to Mollie's secure checkout page (supporting iDEAL, Bancontact, Credit Card, Apple Pay, and other configured payment methods).
5. **Webhook Processing (`POST /payment/webhook/mollie`)**: Mollie asynchronously notifies the application of payment status updates. The application securely verifies payment status directly via the Mollie API, updates the order to `paid` and `confirmed`, and dispatches the queued `PaymentConfirmation` email.
6. **Order Confirmation & Thank-You (`GET /order-confirmed/{order}`)**: Displays order details, items breakdown, pricing, and live payment status. Protected by authorization checks: authenticated users can only view their own orders, and guest visitors can only view orders placed during their current session.
7. **Payment Recovery**: If a payment is cancelled or fails at the gateway, the customer is directed back to their order page with an alert and an immediate "Pay now" retry button.

### Architecture and Configuration

- **Abstraction**: `App\Contracts\PaymentProvider` and `App\Services\PaymentService` allow swapping or adding additional payment gateways (e.g., Stripe, PayPal) without modifying controller or checkout logic.
- **Provider Implementation**: `App\Payments\MollieProvider` integrates with `mollie/laravel-mollie` (v5) and handles payment creation, webhooks, and status mapping.
- **Configuration**: Defined in `config/payment.php` and configured through `.env`:
  - `PAYMENT_PROVIDER=mollie`: Active gateway provider.
  - `MOLLIE_KEY=test_...`: Mollie API key.
  - `PAYMENT_WEBHOOK_URL`: Optional custom webhook override for testing with local tunnels (e.g. Ngrok, Expose, Herd Share).
- **Security**: The webhook endpoint `payment/webhook/*` is excluded from CSRF verification in `bootstrap/app.php` to accept provider callbacks. Webhooks query Mollie's API directly for verified payment status to prevent tampering.
- **Queueing**: Payment confirmation emails use `App\Mail\PaymentConfirmation` and implement `ShouldQueue` so email delivery does not block webhook response execution.
- **Rate limiting**: All cart POST endpoints (`cart.add`, `cart.items.update`, `cart.items.remove`, `checkout.store`, `checkout.pay`) are protected by `throttle:10,1` middleware (10 requests per minute per IP) to prevent abuse and stock manipulation attempts.

### Inclusive VAT Pricing

All product prices in the CMS are **VAT-inclusive** — the price entered by the webshop manager is exactly what the customer pays. The system calculates the net subtotal and tax amount automatically at order creation.

- **Global tax rate**: Configurable via `TAX_RATE` in `.env` (default `21` for Belgium).
- **Per-product override**: Each product and variant has a `tax_rate` column (nullable). When `null`, the global rate applies. Variants inherit from their product when their own `tax_rate` is `null`.
- **Order tax calculation**: At order creation, `CartService::convertToOrder()` calculates per-item net price and tax amount, storing `subtotal` (net), `tax_amount`, and `total` (gross) on the `Order` model. The Filament admin panel's OrderForm derives the tax amount from the gross subtotal using the same configured tax rate.
- **Tax rate snapshot**: The tax rate at the time of adding to cart is stored on `CartItem`, ensuring historical accuracy if a product's tax rate changes later.
- **CMS price input**: Price fields in the Filament product form are labeled "Price (incl. VAT)" with helper text. A tax rate select (0%, 6%, 12%, 21%) allows per-product overrides.
- **Public price display**: Product pages show a VAT notice (e.g., "incl. 21% VAT"). The thank-you page displays the order breakdown with "(excl. VAT)" next to the net subtotal and "(incl. VAT)" next to the total.
- **Email templates**: Payment confirmation emails include "(incl. VAT)" next to the order total.

## Filament CMS

A separate documentation file is available for the Filament CMS implementation:  
[FILAMENT.md](FILAMENT.md)

## Composer Package Dependencies

A separate documentation file is available for the Composer package dependencies:
[COMPOSER_PACKAGES.md](COMPOSER_PACKAGES.md)

Highlighted packages:

- **Laravel Boost** - AI skills for the Junie assistant and other agents
- **Laravel Mollie** - Payment gateway integration for hosted checkouts and webhooks
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
