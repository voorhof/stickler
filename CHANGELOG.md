# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/voorhof/stickler/compare/v1.8.4...HEAD)

### Security

- Added dynamic HTTP cache headers middleware (`SetDynamicCacheHeaders`) and synchronized response cache bypass logic to prevent public CDN and shared proxy cache poisoning on dynamic user-facing routes
- Implemented administrative lockout prevention in `UserPolicy`, `UserObserver`, `AccountController`, and Filament `RoleSection` to prevent deleting, permanently purging, or demoting the last active administrator
- Added dynamic negative lookahead regex route constraint to catch-all `/{page}` route in `routes/web.php` compiled from `config('sluggable.excluded_from_slug')` to prevent system route shadowing
- Fixed SQL injection risk in CartService by removing `DB::raw()` usage
- Added webhook signature verification for Mollie payments
- Added rate limiting to password reset endpoints (`throttle:3,1` and `throttle:5,1`)
- Added rate limiting to payment webhook endpoint (`throttle:30,1`)
- Added null checks in password reset activity logging
- Fixed activity log ordering in account deletion (log before delete)

### Performance

- Bound `Jaybizzle\CrawlerDetect` as a container singleton in `AppServiceProvider` and injected into `TrackPageView` to eliminate redundant regex recompilation across HTTP requests
- Converted `GenerateSitemap` to model-driven sitemap generation querying published records directly instead of executing loopback HTTP crawling
- Fixed N+1 queries in CartService with eager loading
- Fixed N+1 query in ProductVariant::getLabelAttribute() with `relationLoaded` check
- Fixed N+1 query in RenderResponsiveRichContent trait with `relationLoaded` check
- Fixed N+1 queries in Cart model methods (`totalQuantity`, `isEmpty`, `getItemsSumInCents`)
- Fixed race condition on `$model->fresh()` in CartService

### Bug Fixes

- Implemented pessimistic row locking (`lockForUpdate()`) and atomic stock validation in `CartService` during checkout to prevent concurrent race-condition overselling
- Added inventory quantity clamping and transaction isolation in `Cart::merge()` when merging guest carts into user carts
- Enabled `after_commit` across queue connections in `config/queue.php` and wrapped transactional mail delivery in `StoreContactMessage` to prevent queue workers processing jobs before database commits
- Fixed race condition in contact form duplicate detection (moved inside `DB::transaction()` with `lockForUpdate()`)
- Added retry logic for order number generation on unique constraint violation
- Added null message check in SendMessageReplyMailAction
- Added unique lock to sitemap job with `ShouldBeUniqueUntilProcessing` and explicit `uniqueId()`
- Fixed policy methods with strict type hints (removed nullable `$user` parameters)

### New Features

- Added `App\Actions\Sluggable\GenerateSlugAction` extending Spatie Sluggable to treat `config('sluggable.excluded_from_slug')` as reserved keywords and auto-increment clean numeric suffixes across all models
- Added `AdministrativeLockoutException` for explicit model-level and policy-level admin lockout handling
- Added `WebhookResult` enum for structured webhook responses
- Added `InvalidWebhookSignatureException` for webhook security
- Improved webhook error responses with proper HTTP status codes
- Added `boost:install` to post-install scripts
- Added composer cooldown policy

## [v1.8.4](https://github.com/voorhof/stickler/compare/v1.8.3...v1.8.4) - 2026-11-06

### What's Changed

* fixes/updates by @voorhof in https://github.com/voorhof/stickler/pull/32

1. Added boost:install with --no-interaction to the installation process
2. Added cooldown period to composer
3. Improved the payment webhook security
4. Fixed N+1 query problem in CartService
5. Fixed SQL Injection Risk in CartService via DB::raw()
6. Fixed nullable user without null checks
7. Fixed eager-loading the cartItems relationship
8. Added signature verification for webhook handler
9. Fixed convertToOrder() relationship cartItems re-queries
10. Fixed race condition on $model->fresh() in CartService
11. Added null checks to $user before logging activity in password controllers
12. Added a retry order number generation on unique violation
13. Fixed race condition in Contact Form duplicate detection
14. Added rate limiting middleware to password forgot and reset endpoints
15. Added check in RenderResponsiveRichContent to avoid lazy loading media records when the relationship isn't pre-loaded
16. Added defensive check in getLabelAttribute() to prevent lazy loading
17. Fixed the N+1 query issue in ProductVariant::getLabelAttribute()
18. Added message null check to SendMessageReplyMailAction
19. Added unique lock to sitemap job for potential duplicate sitemap generation
20. Composer & NPM updates
21. Updated README

**Full Changelog**: https://github.com/voorhof/stickler/compare/v1.8.3...v1.8.4

## [v1.8.3](https://github.com/voorhof/stickler/compare/v1.8.2...v1.8.3) - 2026-11-06

### What's Changed

* fixes/updates by @voorhof in https://github.com/voorhof/stickler/pull/31

1. Added lazyLoaded to section schema loading
2. Implemented automatic stock quantity check in cart and checkout
3. Update .gitattributes to ignore SECURITY.md
4. Updated README
5. Composer updates

**Full Changelog**: https://github.com/voorhof/stickler/compare/v1.8.2...v1.8.3

## [v1.8.2](https://github.com/voorhof/stickler/compare/v1.8.1...v1.8.2) - 2026-11-05

### What's Changed

* fixes/updates by @voorhof in https://github.com/voorhof/stickler/pull/30

1. Boost update
2. Compatibility improvements for the Laravel installer
3. Installation defaults
4. Welcome page layout update
5. Composer & NPM updates

**Full Changelog**: https://github.com/voorhof/stickler/compare/v1.8.1...v1.8.2

## [v1.8.1](https://github.com/voorhof/stickler/compare/v1.8.0...v1.8.1) - 2026-11-03

### What's Changed

* v1.8.1 - fixes/updates by @voorhof in https://github.com/voorhof/stickler/pull/29

1. Created RegisterRequest for storing in RegisteredUserController
2. Added delete account functionality to user profile
3. Moved variant to sub ProductResource in sidenav
4. Added confirmation modal before logout
5. Added 'print order' button to order details page
6. Refactored form input fields to components
7. Composer updates

**Full Changelog**: https://github.com/voorhof/stickler/compare/v1.8.0...v1.8.1

## [v1.8.0](https://github.com/voorhof/stickler/compare/v1.7.2...v1.8.0) - 2026-11-02

### What's Changed

* User authentication and payments with Mollie integration by @voorhof in https://github.com/voorhof/stickler/pull/28

1. Created user Auth controllers, routes, views and tests
2. Created MergeGuestCart middleware for login to merge guest cart with authenticated user cart
3. Added Logout buttons to nav and dashboard
4. Added Payment integration with Mollie
5. Added Tax calculations and refactored order processing
6. Added Order overview to user account
7. Added Profile edit details page to user account
8. Added login/register option for guest users on cart and checkout page
9. Added API rate limiting to cart and checkout operation POST routes
10. Show latest orders on the user dashboard
11. Added export to Orders resource in Filament
12. Updated README
13. Composer & NPM updates
14. Typo fix
15. Fix CartItem and OrderItem observer tests with quantity update different from possible faker values
16. Nullify email_verified_at on email change
17. Added ProtectAgainstSpam to register route

**Full Changelog**: https://github.com/voorhof/stickler/compare/v1.7.2...v1.8.0

## [v1.7.2](https://github.com/voorhof/stickler/compare/v1.7.1...v1.7.2) - 2026-10-30

### What's Changed

* Order confirmation by @voorhof in https://github.com/voorhof/stickler/pull/27

1. Added extra flow to checkout with order confirmation for address fields and thank you page
2. Updated CartSeeder to use round-robin distribution across users instead of random assignment

**Full Changelog**: https://github.com/voorhof/stickler/compare/v1.7.1...v1.7.2

## [v1.7.1](https://github.com/voorhof/stickler/compare/v1.7.0...v1.7.1) - 2026-10-30

### What's Changed

- System fixes and updates by @voorhof in https://github.com/voorhof/stickler/pull/26

1. Added cooldown to dependabot.yml with default-days: 3
2. Added Plumb check badge to README
3. Changed the Cart nav-item to Order sub-item in Filament sidebar
4. Added ConsoleTest for schedule check of console.php
5. Added RedirectTest for checking redirects defined in redirect.php
6. Fixed translation: Created BusinessHoursPlugin that overrides getNavigationGroup()
7. Fixed calculated price fields in the Order form: now rounded to 2 decimals
8. Created LocaleSelect for UserForm and EditProfile page
9. Added CategoriesSection to content models resource forms in Filament
10. Moved tags and categories to new seperate NavigationGroup 'Classifications'
11. Added categories to default activity logging
12. Fix double activitylog when creating items with observer trait DisablesActivityLogOnSecondSave
13. Fix missing log of content field when creating new record
14. Added Variants and Items to activity logging
15. Refactored Order::items() to orderItems() and Cart::items() to cartItems()
16. Pint style fixes
17. Composer and NPM updates

- **Full Changelog**: https://github.com/voorhof/stickler/compare/v1.7.0...v1.7.1

## [v1.7.0](https://github.com/voorhof/stickler/compare/v1.6.1...v1.7.0) - 2026-10-30

### What's Changed

* Shopping Cart e-commerce additions by @voorhof in https://github.com/voorhof/stickler/pull/25
  
* Update CHANGELOG
  
* Created Cart and CartItem models, migrations, factories, seeders, policies, observers, service and testfiles
  
* Created Cart Filament resource
  
* Created public CartController, routes, blade views, extras and testfiles
  
* Web routes fix: put PageController last
  
* Created PendingOrdersWidget
  
* Fixed $cartItemCount for auth users
  
* Changed blade @csrf to <x-csrf-input/> component
  
* Update README
  
* Pint fix
  

**Full Changelog**: https://github.com/voorhof/stickler/compare/v1.6.1...v1.7.0

## [v1.6.1](https://github.com/voorhof/stickler/compare/v1.5.3...v1.6.1) - 2026-10-29

### What's Changed

* Updated actions in test.yml by @voorhof in https://github.com/voorhof/stickler/pull/24

**Full Changelog**: https://github.com/voorhof/stickler/compare/v1.6.0...v1.6.1

## [v1.6.0](https://github.com/voorhof/stickler/compare/v1.5.3...v1.6.0) - 2026-10-29

### What's Changed

* Products and Orders e-commerce additions by @voorhof in https://github.com/voorhof/stickler/pull/18
* Update CHANGELOG
* Update changelog.yml
* GitHub Actions used in the workflows are pinned to a full commit SHA
* Added dependabot configuration for composer, npm and github-actions
* Added basic brandbook
* Added icons to blade components
* Added brandbook and contact page links to welcome blade view
* Changed footer logo size
* Created Product model, migration, factory, seeder, policy, observer and testfiles
* Created Product Filament resource
* Added public Product routes and views
* Added 'products.*' routes to TrackPageView middleware
* Changed is_discounted form field from checkbox to toggle
* Updated ProductFactory to make sure discount_price is lower then price field
* Updated lang files
* Created ProductVariant model with ProductVariantOption and ProductVariantOptionType
* Added missing response cache flush tests to Observer testfiles
* Updated public Product controller & blade for displaying variants
* Added extra tag relation to models
* Created Category model, migration, factory, seeder, policy, observer, trait and testfiles
* Created Category Filament resource
* Updated public Product controller & blade for displaying categories
* Added getCoverImageUrl to HasMediaConversions for webp conversion fallback to original image
* Created Order and OrderItem models, migrations, factories, seeders, policies, observers, enums and testfiles
* Created Order Filament resource
* Choose products from select when creating orders
* Added activity logging to ProductVariantOptionType model
* Added SKU label to product variants repeater
* Create ceo user first, before admin user (ceo = id 1)
* Fix testfiles
* Composer & NPM updates

**Full Changelog**: https://github.com/voorhof/stickler/compare/v1.5.3...v1.6.0

## [v1.5.3](https://github.com/voorhof/stickler/compare/v1.5.2...v1.5.3) - 2026-10-26

### What's Changed

- Backend updates by @voorhof in https://github.com/voorhof/stickler/pull/17
- Added UsePolicy to PageView model
- Updated testcases
- Created Actions for contact message and sitemap creation
- Created HasPublishedScope for publishable models and updated controllers
- Added automatic deferred Spatie response-cache invalidation for public content changes.
- Added a becamePublishedBetween scope and console ClearScheduledContentCacheCommand
- Deferred page-view tracking
- Queued media conversions
- Queued Sitemap generation with job with automatic dispatching in observers
- Added database indexes to page_views, posts, pages, and projects
- Updated RenderResponsiveRichContent to remove N+1
- Updated public show pages to return 404 instead of redirecting unpublished records
- Replaced Model::preventLazyLoading() with Model::shouldBeStrict() in AppServiceProvider
- Refactored various files to function under the Eloquent strict mode
- Use `^11.23` for `spatie/laravel-medialibrary`
- Refactored admin GET request for locale and sitemap to POST action
- Disabled Telescope in production
- Composer & NPM updates
- Updated readme files

**Full Changelog**: https://github.com/voorhof/stickler/compare/v1.5.2...v1.5.3

## [v1.5.2](https://github.com/voorhof/stickler/compare/v1.5.1...v1.5.2) - 2026-10-24

### What's Changed

- General backend CMS optimisations by @voorhof in https://github.com/voorhof/stickler/pull/16
- Added extra badges to README.md
- Added CHANGELOG.md and GitHub workflow for updating changelog
- Added Github GitHub Community Standards files
- Added Model::preventLazyLoading to AppServiceProvider
- UserResource::getEloquentQuery() eager-loads `roles`
- Added failure handling and duplicate protection to ContactController::store()
- Moved opening-hours window logic out of the controller into a testable service
- Filament CMS code improvements
  * Fixed the Filament `authorize()` finding: seven actions passed a pre-evaluated boolean, which Filament does not expect as an ability or closure.
  * Permission checks now run lazily, so each action is only authorized when used.
  * Replaced the correlated subquery used to sort the Users table role column with a deterministic aggregate alias.
  * The role column sorts on `primary_role_name`, which is computed once per user in the resource query.
  * Consolidated the duplicated bulk status-toggle logic (archive, dearchive, publish, unpublish, complete) into one shared base class.
  * Action behaviour is unchanged: each action keeps its own label, notifications, icon, color, and filter visibility.
  * Added focused Filament tests for UsersTable filters and for the restricted-user access rules of converted actions. All 136 tests in the affected Feature and Unit files pass.
  * Re-checked the Unit Filament test files: most already cover permissions, but several action test files still have no direct restricted-user check.
  

**Full Changelog**: https://github.com/voorhof/stickler/compare/v1.5.1...v1.5.2

## [1.5.1](https://github.com/voorhof/stickler/compare/v1.5.0...v1.5.1) - 2026-10-24

- Updated PageViewsWidget layout
- Fixed SQL error on MySQL with strict grouping in the page views table
- Composer security updates (Livewire: authorization bypass in `#[Authorize]`)

## [1.5.0](https://github.com/voorhof/stickler/compare/v1.4.0...v1.5.0) - 2026-10-24

- General UI updates and backend CMS optimisations (#13)
- Added basic layout to index and show pages
- Created `TracksCreatorAndUpdater`, `HasCreatorAndUpdater`, `HasMediaConversions` and `HasStandardMediaCollections` traits
- Added `#[UsePolicy(UserPolicy::class)]` to the User class
- Created `HasStandardActivitylogOptions` trait
- Centralized observer and policy patterns
- Optimized test files
- Added Blade formatting to Laravel Pint
- Activity log for message reply mails sent in `SendMessageReplyMailAction`
- Added Bootstrap 5 paginator to `AppServiceProvider` and index pages
- Show fallback cover image with `getFirstMediaUrl()` on index pages
- Installed Spatie Opening Hours and the Filament plugin
- Added exceptional dates and holiday periods to the contact show page
- Added caching to BusinessHours
- Added PageView model and page view tracking middleware
- Added artisan `CleanPageViewsCommand` for scheduled clean-up
- Added bot filtering to the TrackPageView middleware
- Added `last_visited_at` column to PageViewsTable
- Fixed PageViewsTable search
- Fixed language file key ordering

## [1.4.0](https://github.com/voorhof/stickler/compare/v1.3.0...v1.4.0) - 2026-10-21

- Dev 12 (#12)
- Updated Page layout to one column for `renderResponsiveRichContent`
- Added "Skip to main content" button
- Created `ClearResponseCacheAction` for manual clearing of the Spatie Response Cache
- Added verify email actions
- Added "unverified users" filter to UsersTable
- Added import and export actions to Filament tables
- Added "File management" navigation group in the CMS sidebar (media and backups)
- Fixed missing translations
- Updated README with Node modules information
- Composer and NPM updates

## [1.3.0](https://github.com/voorhof/stickler/compare/v1.2.0...v1.3.0) - 2026-10-19

- Page, Post and Project public views with media conversions rendering (#11)
- Created Page model, migration, factory, seeder, policy, observer and tests
- Created Page public controller, routes, views and tests
- Created Page Filament resource
- Added slug string exclusions for Page slugs
- Added public Post and Project routes and views
- Updated offcanvas sidebar menu
- Added `.c-rich-content` CSS
- Created `RenderResponsiveRichContent` trait for responsive images inside rich content
- Added webp media conversion to models using Spatie Medialibrary
- Added `maxHeight` to RichEditor default settings
- Added view and edit links on public show pages and published Filament resources
- Optimized backups CMS page speed
- Added ffmpeg static file for installs without remote downloads
- Composer and NPM updates

## [1.2.0](https://github.com/voorhof/stickler/compare/v1.1.1...v1.2.0) - 2026-10-15

- Dev 10 (#10)
- Refactored model slug `getRouteKeyName()` to the `#[RouteKey]` attribute
- Added conditional checks to social links in Blade
- Refactored `Fit::Contain` to `Fit::Crop`
- Refactored `addMediaConversion('preview')` to 400px
- Added SourceCodePlugin for HTML editing in the Rich Editor
- Fixed UnreadMessagesWidget width
- Added `GenerateMissingResponsiveImagesCommand`
- Installed Laravel Head package
- Optimized unit and feature test files
- Removed `laravel-lang/lang` from composer.json
- Added mailing via `SendMessageReplyMailAction` with `ContactMessageReply`
- Composer and NPM updates

## [1.1.1](https://github.com/voorhof/stickler/compare/v1.1.0...v1.1.1) - 2026-10-09

- Branch 9 updates (#9)

## [1.1.0](https://github.com/voorhof/stickler/compare/v1.0.4...v1.1.0) - 2026-09-28

- Dev 12 (#8)

## [1.0.4](https://github.com/voorhof/stickler/compare/v1.0.3...v1.0.4) - 2026-09-06

- General frontend updates (#7)

## [1.0.3](https://github.com/voorhof/stickler/compare/v1.0.2...v1.0.3) - 2026-09-06

- Updated README (#6)

## [1.0.2](https://github.com/voorhof/stickler/compare/v1.0.1...v1.0.2) - 2026-09-06

- Updated Composer with database seeding (#5)

## [1.0.1](https://github.com/voorhof/stickler/compare/v1.0.0...v1.0.1) - 2026-09-06

- Updated tests with pest-plugin-drift reformat (#4)

## [1.0.0](https://github.com/voorhof/stickler/releases/tag/v1.0.0) - 2026-09-05

- Initial release: package installation and setup (#1), frontend Blade UI setup (#2), Bootstrap toolkit and preprocessor updates (#3)
