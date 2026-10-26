# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
