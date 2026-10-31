# Filament Implementation Tutorial

This tutorial provides an overview of the Filament implementation for the admin CMS used in this project.

## Overview

[Filament v5](https://filamentphp.com/docs/5.x) has been installed to provide a powerful and customizable admin panel.
The implementation focuses on clean code separation by moving form and table definitions in their own dedicated classes.

### Installation Details

The following commands were used to install and set up Filament with an initial generated User crud resource.
It's not necessary to run these commands again for local development since they are already included in the repository.
We just provide them here for reference.

```bash
composer require filament/filament:"^5.10"
php artisan filament:install --panels
php artisan vendor:publish --tag=filament-config
php artisan icons:cache
php artisan make:filament-resource User --generate --soft-deletes
php artisan make:provider Filament/UiServiceProvider        
```
- Required the `filament/filament` package, version 5.10 at the time of writing.
- Install command created the default admin panel at `app/Providers/Filament/AdminPanelProvider.php`.
- Published the config file, located in `config/filament.php`.
- Cached the Filament icons using `icons:cache` command.
- Resource files created for the `User` model in the `app/Filament/Resources/Users` directory.
- We created an extra custom provider `App\Providers\Filament\UiServiceProvider.php` for global UI customizations.

### Translation files

Language files for the `en` and `nl` languages are located in `lang/vendor/filament` and other vendor packages.
Translations use standard ISO 639-1 base language codes (`en`, `nl`).

These commands will (re-)publish the language files:

```bash
php artisan vendor:publish --tag=filament-translations
php artisan vendor:publish --tag=filament-actions-translations
php artisan vendor:publish --tag=filament-forms-translations
php artisan vendor:publish --tag=filament-infolists-translations
php artisan vendor:publish --tag=filament-notifications-translations
php artisan vendor:publish --tag=filament-panels-translations
php artisan vendor:publish --tag=filament-schemas-translations
php artisan vendor:publish --tag=filament-tables-translations
php artisan vendor:publish --tag=filament-widgets-translations
```

The Laravel framework's own language files have also been published (and customized).
You can find them inside the `lang` root folder (`lang/en`, `lang/nl`, `lang/en.json`, `lang/nl.json`).

This command will (re-)publish the English language files:

```bash
php artisan lang:publish
```

For the FilePond package, used for file uploads by Filament, 
translations for 'nl_BE' are aliased from 'nl' via an extra script in file-upload-locale-alias.blade.php;
which is called inside the AdminPanelProvider.php file.
This provides valid Dutch translations for the FilePond package when using the 'nl_BE' locale.

### Project Structure

We follow a structured approach for our resources to keep the main resource class clean and manageable.

```text
app/Filament/Resources/Users/
├── Pages/              # Specific pages for the resource (List, Create, Edit)
├── Schemas/            # Form definitions using Filament Schemas
│   └── UserForm.php    # Defines the fields for creating/editing users
├── Tables/             # Table definitions
│   └── UsersTable.php  # Defines columns, filters, and actions for the user list
└── UserResource.php    # Main resource class connecting everything
```

## Accessing the Admin Panel

The admin panel is available at:
[https://stickler.test/admin](https://stickler.test/admin)

The User model implements the `Filament\Models\Contracts\FilamentUser` interface,
together with the `canAccessPanel` method.
Implementing Filament’s FilamentUser contract tells Filament how to decide whether
an authenticated user may access a Filament panel.

The User model also implements the `Filament\Models\Contracts\HasName` interface,
together with the `getFilamentName` method, which allows Filament to display the user's name in the admin panel.

Note: Ensure you have a user with admin permissions for local development access. You can set this in your .env file:

```dotenv
ST_ADMIN_NAME="John Doe"
ST_ADMIN_EMAIL="admin@example.com"
ST_ADMIN_PASSWORD="password"
```

After changing the .env admin values to your liking, (re-)run the database migrations and seeders:

```bash
php artisan migrate:fresh --seed
```

## Key Components

### 1. Admin Panel Provider
Located at `app/Providers/Filament/AdminPanelProvider.php`, this is the heart of the Filament configuration.
It defines:
- The path to access the panel (`/admin`).
- Colors, navigation, and middleware.
- Automated discovery of resources, pages, and widgets.
- The locale user-menu actions submit `POST` requests to save the signed-in user's locale preference.
- The Telescope navigation item is hidden in production; Telescope itself is not registered there. See [COMPOSER_PACKAGES.md](COMPOSER_PACKAGES.md#laravel-telescope).

### 2. UI Service Provider

The `UiServiceProvider` is responsible for default UI customization.

### 3. User Resource
Located at `app/Filament/Resources/Users/UserResource.php`,
this resource class defines the model (`User`) and how it's represented in the admin panel navigation.
Other resources are similarly configured.

In Filament v5, we use the `form(Schema $schema)` method to define our forms:

```php
public static function form(Schema $schema): Schema
{
    return UserForm::configure($schema);
}
```

### 4. User Form (Schema)
Located at `app/Filament/Resources/Users/Schemas/UserForm.php`.
Instead of defining the form directly in the resource, we use a dedicated schema class.
This makes it easier to reuse or modify the form logic.

### 5. Users Table
Located at `app/Filament/Resources/Users/Tables/UsersTable.php`.
This class handles the table configuration, including:
- **Columns**: Searchable name and email, formatted dates.
- **Filters**: Filter for verified users and a `TrashedFilter` for soft deletes.
- **Actions**: Edit, Delete, Force Delete, and Restore.

## Working with Soft Deletes

Since the `User` resource was generated with `--soft-deletes`, it includes:
- `TrashedFilter` in the table.
- `SoftDeletingScope` handling in the `UserResource`.
- Restore and Force Delete actions in the table's bulk actions.

## Adding a New Resource

To add a new resource following this pattern:
### Automatic Generation
1. Generate the resource: `php artisan make:filament-resource YourModel --generate`
2. Update the generated resource files to your needs.
### Manual Generation
1. Create `Pages`, `Schemas` and `Tables` directories within a new resource folder. Follow the Project Structure standards.
2. Create form and table logic into dedicated classes and namespace (e.g., `App\Filament\Resources\YourModel\Schemas\YourModelForm` and `App\Filament\Resources\YourModel\Tables\YourModelTable`).
3. Create pages into the dedicated classes and namespace (e.g., `App\Filament\Resources\YourModel\Pages\ListYourModels`).
4. Create and update `YourModelResource` to use these classes.

## Database Notifications

Filament provides a convenient way to manage [database notifications](https://filamentphp.com/docs/5.x/notifications/database-notifications#sending-database-notifications). 

The `databaseNotifications()` method is used inside the `Filament\AdminPanelProvider` class to register the notification observer.

We use the default Filament notifications and persist them to the database after dispatching them:

```php
$notification->sendToDatabase(auth()->user(), isEventDispatched: true)
```

Migration file `0001_01_01_000008_create_notifications_table.php` is included to create the `notifications` table.

For notifications to be processed, ensure your queue is running to receive the notifications:

```bash
php artisan queue:work --queue=high,default,media,low
```


## Rich Editor Source Code

Filament's TipTap `RichEditor` does not ship a tool to view or edit the HTML of its content.
We added a custom **"Source code"** toolbar button (code bracket icon) that opens a modal with an HTML `CodeEditor`,
where the rendered HTML can be previewed and edited.
On submit, the edited HTML is sent back into the editor.

The implementation is based on the `SourceCodePlugin` of the [awcodes/richer-editor](https://github.com/awcodes/richer-editor) package,
but is part of the project itself, so no extra dependency is required.

### SourceCodePlugin

Located at `app/Filament/RichEditor/Plugins/SourceCodePlugin.php`,
this class implements Filament's `RichContentPlugin` contract and provides:
- **Editor tool**: a `RichEditorTool` named `sourceCode` (`SourceCodePlugin::NAME`).
  It reads the HTML from the live JS editor with `$getEditor().getHTML()`, so changes that are not yet synced to Livewire are included.
- **Editor action**: an `Action` with the same name, that opens the modal (sticky footer, default width `Width::FiveExtraLarge`).
  The HTML is formatted for readability with PHP 8.4's HTML5 parser (`Dom\HTMLDocument`): every top-level element is put on its own line.
  On submit, the HTML is sent to the editor with the `setContent` editor command. Empty content falls back to `<p></p>`.
- No extra TipTap PHP or JS extensions.

### Registration

The plugin is registered globally for every `RichEditor` inside the `UiServiceProvider` (`app/Providers/Filament/UiServiceProvider.php`),
and the button is added as its own group in the toolbar:

```php
use App\Filament\RichEditor\Plugins\SourceCodePlugin;
use Filament\Forms\Components\RichEditor;

RichEditor::configureUsing(function (RichEditor $richEditor): void {
    $richEditor
        ->toolbarButtons([
            ['undo', 'redo'],
            [ToolbarButtonGroup::make(__('Paragraph'), ['paragraph', 'h2', 'h3', 'h4'])],
            [ToolbarButtonGroup::make(__('Alignment'), ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'])],
            ['bold', 'italic', 'underline', 'strike', 'link', 'textColor', 'highlight'],
            ['blockquote', 'codeBlock', ToolbarButtonGroup::make('Lists', ['bulletList', 'orderedList'])],
            ['attachFiles'],
            [SourceCodePlugin::NAME],
        ])
        ->floatingToolbars([
            'paragraph' => [
                'bold', 'italic', 'underline', 'strike', 'link', 'textColor',
            ],
            'heading' => [
                'h2', 'h3', 'h4',
            ],
        ])
        ->maxHeight('80svh')
        ->plugins([
            SourceCodePlugin::make(),
        ])
        ->columnSpanFull();
});
```

The modal width can be changed when registering the plugin:

```php
SourceCodePlugin::make()->modalWidth(Width::ScreenLarge)
```

The button label and modal heading use the `"Source code"` translation key in `lang/en.json` and `lang/nl.json` ("Broncode").

### Limitations

- TipTap only keeps the HTML its schema knows. When applying an edit, unknown tags, classes, `style` attributes,
  `<div>` wrappers, `<iframe>`, `<script>`, ... are silently removed. It is meant for tweaking text, links and headings, not as a free-form HTML editor.
- Keep the `data-id` attribute on images intact, it links the image to its Spatie Media Library attachment.
- Always render rich content with `$model->renderRichContent('content')` or `RichContentRenderer` (which sanitizes the HTML), never with `{!! $model->content !!}`.

### Activity log and the second save

Filament's RichEditor re-saves newly created records to attach media files. This second save fires an `updated` model event, which creates an unwanted duplicate activity log entry.

To prevent this, the `DisablesActivityLogOnSecondSave` observer trait (`app/Observers/Traits/DisablesActivityLogOnSecondSave.php`) disables activity logging when `wasRecentlyCreated` is `true`. The trait is used by the observers for models with RichEditor fields: `PostObserver`, `PageObserver`, `ProjectObserver`, and `ProductObserver`.

After disabling logging, the trait updates the "created" activity's `attributes` property to include the `content` field that was set by the RichEditor's second save. This ensures the "created" activity reflects the final state of the model, including the content.

`wasRecentlyCreated` is not time-based; it is `true` only during the same HTTP request where the model was inserted. It resets to `false` at the start of each new request, so normal updates are unaffected.

### Tests

- `tests/Unit/app/Filament/RichEditor/Plugins/SourceCodePluginTest.php`: HTML formatting, tool, action and modal width.
- `tests/Unit/app/Providers/Filament/UiServiceProviderTest.php`: the toolbar button and tool are registered.
- `tests/Feature/app/Filament/Resources/PostResourceTest.php`: opens and submits the action on the Edit Post page.


## Store Management

The store resources manage the product catalogue, categories, orders and carts. They follow the same structure as the other resources: the main resource class delegates its form and table to the `Schemas` and `Tables` classes.

### Products and product variants

- `ProductResource` (`app/Filament/Resources/Products`) manages products. The variants section (`Schemas/Sections/ProductVariantsSection.php`) adds variants to a product.
- `ProductVariantOptionTypeResource` (`app/Filament/Resources/ProductVariantOptionTypes`) manages option types, such as size or colour. Its edit page shows the `ProductVariantOptionsRelationManager`, where the options of that type are maintained.
- A variant has its own SKU, stock quantity and optional price. A variant price overrides the product price only when it is set.

### Categories

- `CategoryResource` (`app/Filament/Resources/Categories`) manages categories. Categories are assigned to models through the `HasCategories` trait, for example to products.

### Orders

- `OrderResource` (`app/Filament/Resources/Orders`) manages orders. Its table uses the `HasOrderTableDefaults` trait.
- `OrderStatus` and `PaymentStatus` (`app/Enums`) implement Filament's `HasLabel`, so the labels in the table, filters and forms are translated through `lang/en.json` and `lang/nl.json`.

### Carts

- `CartResource` (`app/Filament/Resources/Carts`) manages carts with customer data stored in a JSON column, a guest flag and an expiration date. Carts have no soft deletes, so expired carts are hard deleted.
- `CartItemsRelationManager` lists the items of a cart. When adding an item, you choose a product and, for products with variants, a variant. The item stores snapshots of the name, variant label, SKU and price.
- The **Clear carts** header action (`app/Filament/Actions/CartCleanupAction.php`) asks for a `max_days` value in a confirmation modal and deletes carts not updated within that many days. The default is 30 days.
- The scheduled `carts:cleanup --force` command runs daily at 06:06 and deletes carts whose `expires_at` has passed.
- Each authenticated user can have only one active cart, enforced by a unique constraint on `(user_id, is_guest)` at the database level. The `CartService::getOrCreateCart()` method uses `firstOrCreate` to prevent duplicates at the application level.
- Guest carts are identified by a `cart_token` cookie (30 days). When a guest logs in or registers, the `MergeGuestCart` middleware merges the guest cart into the user's authenticated cart automatically. See the [User Authentication section](README.md#user-authentication) for details.

### Dashboard widget: PendingOrdersWidget

Located at `app/Filament/Widgets/PendingOrdersWidget.php`, this stats widget shows the number of orders with the `Pending` status. The number is shown in the warning color when there are pending orders, and in the success color when there are none. Clicking the widget opens the Orders list with the Pending status filter active.

The link passes the filter with the `filters` query key, the same way `UnreadMessagesWidget` does.

### Tests

- `tests/Feature/app/Filament/Resources/CartResourceTest.php`: the cart list, forms, and the cart items relation manager.
- `tests/Unit/app/Filament/Actions/CartCleanupActionTest.php`: the cart cleanup action.
- `tests/Unit/app/Filament/Widgets/PendingOrdersWidgetTest.php`: the count, the filtered link and the color.
- `tests/Feature/app/Filament/Resources/ProductVariantOptionTypeResourceTest.php` and `tests/Feature/app/Filament/Resources/OrderResourceTest.php`: the variant option types and orders.

## Page Views

Public page views are tracked on the server, without cookies or a consent banner.
The admin panel shows the results in a dashboard widget and in a view-only resource.

### Tracking: PageView model and TrackPageView middleware

- `App\Models\PageView` stores one row per visit in the `page_views` table: `path`, `route_name`, `referrer_host`, `visitor_hash`, `viewed_on` and `created_at`. The table has no `updated_at` column.
- `App\Http\Middleware\TrackPageView` is registered with `prepend` in `bootstrap/app.php`, so it runs before `CacheResponse`. Cached responses are therefore still counted.
- A qualifying view is inserted using Laravel's `defer()` helper after the response has been sent, keeping the database write off the request's response path.
- Only successful `GET` requests (status 200) on these route names are tracked: `welcome.index`, `brandbook.index`, `contact.index`, `terms.*`, `posts.*`, `products.*`, `projects.*`, `pages.*` and `cart.index`. Admin and other routes are never tracked. Cart actions such as adding an item are POST requests and are not tracked.
- The visitor is stored as `hash_hmac('sha256', ip|user-agent, app.key|date)`. The salt changes every day, so the same visitor cannot be followed across days. The raw IP address is never stored.
- Only the referrer host is stored, never the full referrer URL. The referrer is `null` for internal navigation.

To add tracking for another public page, add its route name to `$trackedRouteNames` in `TrackPageView`.

### Bot filter

- `TrackPageView` checks the User-Agent with `jaybizzle/crawler-detect`. Visits from known bots are stored with `is_bot = true` instead of being dropped, so the bot share can be checked over time.
- Browser prefetch requests (`Purpose: prefetch` or `Sec-Purpose: prefetch`) and requests with an empty User-Agent are skipped and not stored.
- Only the boolean flag is stored, never the User-Agent string.
- The `PageViewsWidget` and the `PageViewResource` count only rows with `is_bot = false`.
- Bots that send a browser User-Agent are still counted as human visits.
- Rows created before the `is_bot` column was added have no classification, so their bot hits still count.

### Dashboard widget: PageViewsWidget

Located at `app/Filament/Widgets/PageViewsWidget.php`, this stats widget shows two numbers for today:
- **Page views today**: the number of rows with `viewed_on` equal to today.
- **Unique visitors today**: the number of distinct `visitor_hash` values for today.

Both labels use the `Page views today` and `Unique visitors today` keys in `lang/en.json` and `lang/nl.json`.

### Resource: PageViewResource

Located at `app/Filament/Resources/PageViews/PageViewResource.php`, this view-only resource lists the public pages in the **Settings** navigation group:

- `getEloquentQuery()` groups the rows by `path` and `route_name`. The list shows one row per page with the total views (`views`) and unique visitors (`unique_visitors`).
- The table is defined in `app/Filament/Resources/PageViews/Tables/PageViewsTable.php`. It has columns for path, route name (hidden by default), page views and unique visitors. The default sort is views, descending.
- The **Viewed on** filter has **From** and **Until** date pickers. It filters on the `viewed_on` column, so the counts follow the selected period.
- The resource has only the list page (`ListPageViews`). Creating and editing are disabled through `PageViewPolicy`, which always returns `false` for `create()` and `update()`.
- Access requires the `view pageviews` permission. The permission is added in `database/seeders/AdminUserAndRoleSeeder.php` next to the activity permissions.

Existing admin users do not receive the new permission automatically. Re-run the seeder or assign `view pageviews` manually.

### Limitations

- Bots that do not identify themselves in the User-Agent are still counted. See the bot filter section above.
- The resource has no detail view per page.
- Before production use, the privacy policy should mention this processing. A pseudonymised hash can still be personal data under the GDPR, so a legal review is recommended.

### Cleanup: page-views:clean command and schedule

Old page views can be removed with the `page-views:clean` Artisan command, so the table does not grow forever.

- Located at `app/Console/Commands/CleanPageViewsCommand.php`. It deletes every row whose `viewed_on` is before today minus the given number of days, the same rule as the `PageViewCleanupAction` in the Page views list header.
- `--days` sets how many days to keep (0 to 366, default 60). Values outside this range make the command fail.
- `--force` runs without the confirmation question. Without it, the command asks first and does nothing when you answer no.
- Run it manually with `php artisan page-views:clean --days=60` or `php artisan page-views:clean --days=30 --force`.
- The schedule is defined in `routes/console.php`, next to `activitylog:clean`: `page-views:clean --days=60 --force` runs daily at 06:05.
- The schedule only runs when the Laravel scheduler is active on the server (`php artisan schedule:run` every minute, for example through a cron entry).
- To change the retention, edit the `--days` value in `routes/console.php`.

### Tests

- `tests/Unit/app/Http/Middleware/TrackPageViewTest.php`: tracked routes, the hashed visitor, and the excluded requests.
- `tests/Unit/app/Filament/Widgets/PageViewsWidgetTest.php`: the stats and labels.
- `tests/Unit/app/Models/PageViewTest.php`: the model and table.
- `tests/Unit/app/Policies/PageViewPolicyTest.php`: the create/update block and the permission check.
- `tests/Feature/app/Filament/Resources/PageViewResourceTest.php`: the grouped counts, the date filter, the permission gate, and the create/update block.

## Plugins

List of plugins used in this project.  
Plugins can be found on https://filamentphp.com/plugins

### Resized Column
[Documentation](https://filamentphp.com/plugins/asmit-nepali-resized-column)  
[GitHub](https://github.com/asmitnepali/resized-column)

This plugin allows you to adjust the column width in Filament tables, 
thereby improving the user experience thanks to a more flexible and customisable interface.
The **migration file** has been published in `0001_01_01_000004_create_filament_table_settings_table.php`.

**CSS and JS files** for the plug-in are published in the `public/css/asmit` and `public/js/asmit` directories.

The plug-in is configured in the `plugins()` array of the panel method in the `AdminPanelProvider` class:

```php
use Asmit\ResizedColumn\ResizedColumnPlugin;

->plugins([
    ResizedColumnPlugin::make()
        ->preserveOnDB(),
]);
```

To use the ‘Resize Columns’ feature, you must include the `HasResizableColumn` trait 
in your Filament List Page or your custom page class.
This automatically enables the ‘Resize Columns’ feature for all tables in that source.

```php
use Asmit\ResizedColumn\HasResizableColumn;

class ListUsers extends ListRecords
{
    use HasResizableColumn;

    protected static string $resource = UserResource::class;
}
```

A custom reset button has been added to the column manager trigger in the toolbar.
This button resets the table entirely to its default values, including column widths, filters, sorting, etc.

The blade file for this button is located in `filament.hooks.reset-column-widths-button`.

The `UiServiceProvider` registers the blade file as a render hook:

```php
FilamentView::registerRenderHook(
    TablesRenderHook::TOOLBAR_COLUMN_MANAGER_TRIGGER_AFTER,
    fn () => view('filament.hooks.reset-column-widths-button'),
);
````

This works via the `CanResetTable` trait in `App\Filament\Traits`.
When the `CanResetTable` trait is used on a Filament list page or a custom page class,
the reset button is displayed in the column management trigger in the toolbar,
for example, on the user list page:

```php
use App\Filament\Traits\CanResetTable;
use Asmit\ResizedColumn\HasResizableColumn;

class ListUsers extends ListRecords
{
    use CanResetTable;
    use HasResizableColumn;
}
````

### Filament Business Hours
[GitHub](https://github.com/La-boite-a-code/Filament-Business-Hours)

The **Filament Business Hours** plugin provides a visual interface for managing opening hours in the admin panel.
It stores the schedules in the `business_hours` table and uses `spatie/opening-hours` for opening-hours calculations.

The plugin publishes the following project files:

- Configuration: `config/filament-business-hours.php`
- Migration: `0001_01_03_000000_create_business_hours_table.php`
- Translations: `lang/vendor/filament-business-hours/`
- Frontend assets: `public/css/laboiteacode/filament-business-hours/` and `public/js/laboiteacode/filament-business-hours/`

It is registered in `app/Providers/Filament/AdminPanelProvider.php` through the project's `App\Filament\Plugins\BusinessHoursPlugin`, which extends the package plugin. Access is limited to users who have the `edit settings` permission:

```php
use App\Filament\Plugins\BusinessHoursPlugin;

->plugins([
    // ... other plugins
    BusinessHoursPlugin::make()
        ->navigationSort(9200)
        ->canAccessUsing(fn (): bool => auth()->user()?->can('edit settings') ?? false),
]);
```

The subclass overrides `getNavigationGroup()` to return `__('Settings')` at render time. The navigation group is therefore translated per request, so the business hours item sits in the same **Settings** group as Log Viewer and Telescope in both `en` and `nl`. Do not pass a navigation group to the plugin with `navigationGroup()`, because the override ignores it. The tests are in `tests/Unit/app/Filament/Plugins/BusinessHoursPluginTest.php`, and `tests/Unit/app/Providers/Filament/AdminPanelProviderTest.php` checks that the panel registers this plugin.

The custom `database/seeders/BusinessHoursSeeder.php` seeds a `default` schedule with Monday through Friday
open from `08:00-12:00` and `13:00-18:00`; Saturday and Sunday are closed. The seeder is included in
`DatabaseSeeder` and can be rerun safely because it upserts the `default` record.

The public contact page reads the configured record from `config('filament-business-hours.record', 'default')`:

```php
use LaBoiteACode\FilamentBusinessHours\Models\BusinessHours;
use LaBoiteACode\FilamentBusinessHours\Support\Weekdays;

$openingHours = BusinessHours::openingHours(config('filament-business-hours.record', 'default'));

return view('public.contact', [
    'openingHours' => $openingHours,
    'weekdays' => Weekdays::DAYS,
]);
```

The contact view displays each day's translated name, opening periods, open/closed status, and a highlight for the current day.


### Spatie Media Library
[Documentation](https://filamentphp.com/plugins/filament-spatie-media-library)  
[GitHub](https://github.com/filamentphp/spatie-laravel-media-library-plugin)

More info in the main [README](README.md) file.


### Spatie Settings
[Documentation](https://filamentphp.com/plugins/filament-spatie-settings )  
[GitHub](https://github.com/filamentphp/spatie-laravel-settings-plugin)

More info in the main [README](README.md) file.


### Spatie Tags
[Documentation](https://filamentphp.com/plugins/filament-spatie-tags)  
[GitHub](https://github.com/filamentphp/spatie-laravel-tags-plugin)

More info in the main [README](README.md) file.


## Testing

To test the Filament admin panel and everything related to the Filament setup, 
run the following command:

```bash
php artisan test --filter=Filament
```
