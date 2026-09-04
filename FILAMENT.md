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
composer require filament/filament:"~5.7"
php artisan filament:install --panels
php artisan vendor:publish --tag=filament-config
php artisan icons:cache
php artisan make:filament-resource User --generate --soft-deletes
php artisan make:provider Filament/UiServiceProvider        
```
- Required the `filament/filament` package, version 5.7 at the time of writing.
- Install command created the default admin panel at `App\Providers\Filament\AdminPanelProvider.php`.
- Published the config file, located in `config/filament.php`.
- Cached the Filament icons using `icons:cache` command.
- Resource files created for the `User` model in the `app/Filament/Resources/Users` directory.
- We created an extra custom provider `App\Providers\Filament\UiServiceProvider.php` for global UI customizations.

### Translation files

Language files for the `en_US` and `nl_BE` locales are located in `lang/vendor/filament`.
When publishing the default Filament lang folders, the name for Dutch translations is originally `nl`,
but **we have renamed those folders to `nl_BE`** for consistency with the default Belgian locale.

These commands will (re-)publish the language files, be sure to rename the `nl` folder to `nl_BE` afterward:

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

The Laravel's framework own English language files have also been published (and customized).
You can find them inside the `lang` root folder, together with the translated nl_BE locale version.

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

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ... other configuration
            ->plugins([
                // ... other plugins
                ResizedColumnPlugin::make()
                    ->preserveOnDB(), // Enable database storage (optional)
            ]);
    }
}
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
