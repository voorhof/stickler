# Composer Package Dependencies

All vendor packages used in this project are installed with **Composer**.

## Laravel Boost
[Documentation](https://laravel.com/docs/13.x/boost)  
[GitHub](https://github.com/laravel/boost)

This package is for adding AI skills to the **Junie** assistant and other agents.
AI skills are already included in this repository and are used automatically by the assistant.

```bash
php artisan boost:update --discover --ansi --no-interaction
```

Updating the `boost` command for the skills is automatically configured in `composer.json` inside the `"post-update-cmd"`.

## Laravel Telescope
[Documentation](https://laravel.com/docs/13.x/telescope)  
[GitHub](https://github.com/laravel/telescope)

Laravel Telescope makes a wonderful companion to your local Laravel development environment.
Telescope provides insight into the requests coming into your application, exceptions,
log entries, database queries, queued jobs, mail, notifications, cache operations, scheduled tasks, variable dumps, and more.

**Service Provider** was published to `app/Providers/TelescopeServiceProvider.php`.

The Laravel Telescope provider and the application provider are registered by `AppServiceProvider` only outside production. The package is excluded from automatic provider discovery, so Telescope is not booted in production; its Filament navigation item is also hidden there. The config default for `TELESCOPE_ENABLED` is `false`, while `.env.example` enables it for local and staging environments.

**Migration** file was published to `0001_01_01_000007_create_telescope_entries_table.php`.

**Config** file was published to `config/telescope.php`.

**Environment** variables are included in `.env.example` and used by the config file. The config fallback for `TELESCOPE_ENABLED` is `false`; the example enables it outside production:

```dotenv
TELESCOPE_ENABLED=true
TELESCOPE_PATH=admin/telescope
```

In environments where Telescope is enabled, access is restricted to users with the `access telescope` permission.
This is defined in the `gate()` method of the `TelescopeServiceProvider`:

```php
protected function gate(): void
{
    Gate::define('viewTelescope', function (User $user) {
        return $user->can('access telescope');
    });
}
```

Data pruning is scheduled to run daily by default.  
This is defined in the `routes/console.php` file:

```php
Schedule::command('telescope:prune')
    ->daily();
```

## Laravel Head
[Documentation](https://laravel.com/framework/docs/13.x/head)  
[GitHub](https://github.com/laravel/head)

This package provides a fluent API for defining `<head>` elements in Laravel applications, including titles, metadata, Open Graph tags, favicons, PWA settings, robots instructions, and error pages.

**Service Provider** is registered in `bootstrap/providers.php` and located at `app/Providers/HeadServiceProvider.php`.

Default head tags and error page configurations are defined in `app/Providers/HeadServiceProvider.php`:

```php
Head::defaults(function (HeadBuilder $head) {
    $head
        ->title(config('app.name'), prefix: config('app.name').' | ')
        ->description(config('app.name').' website template')
        ->canonical()
        ->robots([
            RobotsRule::Index,
            RobotsRule::Follow,
            'max-image-preview:large, max-snippet:-1, max-video-preview:-1',
        ])
        ->og(
            type: OgType::Website,
            url: config('app.url'),
            image: config('app.url').'/art/logo/logo_bg.png',
            siteName: str_replace('https://', '', config('app.url')),
            locale: config('app.locale'),
        )
        ->viewport('width=device-width, initial-scale=1')
        ->meta('keywords', mb_strtolower(config('app.name')).', laravel, starter kit')
        ->referrer('strict-origin-when-cross-origin')
        ->applicationName(config('app.name'))
        ->colorScheme('light dark')
        ->appleWebAppTitle(config('app.name'))
        ->webAppCapable()
        ->pwa(
            name: config('app.name'),
            manifest: asset('art/favicons/site.webmanifest'),
            themeColor: '#ffffff',
            appleTouchIcon: asset('art/favicons/apple-touch-icon.png'),
            appleWebAppStatusBarStyle: 'black',
        )
        ->meta('msapplication-TileColor', '#ffffff')
        ->favicon(asset('art/favicons/favicon.ico'), ImageType::Ico)
        ->icon(asset('art/favicons/favicon-16x16.png'), ImageType::Png, '16x16')
        ->icon(asset('art/favicons/favicon-32x32.png'), ImageType::Png, '32x32')
        ->icon(asset('art/favicons/favicon-48x48.png'), ImageType::Png, '48x48')
        ->icon(asset('art/favicons/favicon-96x96.png'), ImageType::Png, '96x96')
        ->icon(asset('art/favicons/favicon.svg'), ImageType::Svg)
        ->maskIcon(asset('art/favicons/favicon.svg'), '#000000')
        ->manifest(asset('art/favicons/site.webmanifest'))
        ->link('sitemap', Storage::url('sitemap.xml'), ['type' => 'application/xml', 'title' => 'Sitemap']);
});

Head::errors(function (ErrorPages $errors) {
    $errors->defaults(robots: 'noindex, follow');

    $errors->status(
        404,
        title: __('Not Found'),
        description: __('Nothing to show.'),
    );

    $errors->status(
        500,
        title: __('Forbidden'),
        description: __('Forbidden'),
    );
});
```

The `@head` Blade directive is included in the base layout (`resources/views/components/layouts/app.blade.php`) within the `<head>` tag to render all generated elements:

```blade
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @stack('scripts-head-top')
    
    {{-- Laravel HeadServiceProvider  --}}
    @head
    
    {{-- Styles / Scripts --}}
    @fonts
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
```

Page-specific titles and descriptions can be customized fluently in controllers or routes using the `Head` facade:

```php
use Laravel\Head\Facades\Head;

public function index(): View
{
    Head::title('Contact')
        ->description('Contact details '.config('app.name'));

    return view('public.contact');
}
```

## Spatie Sluggable
[Documentation](https://spatie.be/docs/laravel-sluggable/v4/introduction)  
[GitHub](https://github.com/spatie/laravel-sluggable)

This package is used to generate route slugs for models.

The **config** file is located in `config/sluggable.php`.

The `#[Sluggable()]` attribute is added to models to automatically generate slugs when they are created,
for example, in the User model:

```php
#[Sluggable(
    from: 'name',
    to: 'slug',
    separator: '-',
    language: 'nl',
    maxLength: 48,
    unique: true,
    onUpdate: false,
)]
```

The `#[RouteKey('slug')]` attribute is added to models so it overrides the `getRouteKeyName` method within its class,
so that route binding works based on the slug rather than the ID:

```php
#[RouteKey('slug')]
```

### Excluded Slugs Configuration (excluded_from_slug)

A custom configuration array excluded_from_slug is defined in config/sluggable.php to blacklist reserved keywords and system route names (e.g. contact, admin, pages, privacy-policy, cart, products, etc.):

```php
'excluded_from_slug' => [
    'admin',
    'algemene-voorwaarden',
    'brandbook',
    'cart',
    'contact',
    'cookie-policy',
    'filament',
    'home',
    'pages',
    'page',
    'posts',
    'post',
    'privacy-policy',
    'products',
    'product',
    'projects',
    'project',
    'storage',
    'test',
    'up',
    'welcome',
    'xxx',
    'yyy',
    'zzz',
],
```

The `products`, `product`, `cart` and `brandbook` entries were added for the store and public routes, so a page or product can never take a slug that collides with `/products`, `/product`, `/cart` or `/brandbook`.

This list is used across the application:
1. Filament Form Validation (SlugInput): The reusable SlugInput component (App\Filament\Schemas\Components\SlugInput) applies Rule::notIn(config('sluggable.excluded_from_slug')) to prevent users from manually entering reserved slugs in the admin panel.
2. Model Observers (PageObserver): During creating and updating model events in PageObserver, the model checks if the assigned slug is within config('sluggable.excluded_from_slug'). If matched, a unique timestamp suffix (now()->format('-y-m-d-H-i-s')) is automatically appended to avoid route collisions with existing system routes.

## Spatie Permission
[Documentation](https://spatie.be/docs/laravel-permission/v8/introduction)  
[GitHub](https://github.com/spatie/laravel-permission)

This package is used to manage user permissions and roles.
Create new roles in the Filament admin environment and assign permissions to them.

The **User** model has this trait:

```php
use HasRoles;
```

We [extended](https://spatie.be/docs/laravel-permission/v8/advanced-usage/extending#content-extending-role-and-permission-models) the default Spatie Role and Permission models
to our own app **Role** and **Permission** models inside the `app/models` folder.
This is helpful for managing roles and permissions inside Filament (RoleResource).

For this to work, the permission config file was updated to use our own models:

```php 
'permission' => App\Models\Permission::class,
'role' => App\Models\Role::class,
```

**Config** file was published to `config/permission.php`.

**Migration** file was published to `database/migrations/0002_01_01_000000_create_permission_tables.php`.

A custom **seeder** `AdminUserAndRoleSeeder.php` was created to seed the permissions and roles,
and create a default admin user.

A "Super Admin" role needs no permissions set in the database,
access is automatically granted because of the **Gate::before()** added in the boot method of the `AccessServiceProvider` (`app/Providers/Spatie/AccessServiceProvider.php`):

```php
public function boot(): void
{
    // Implicitly grant the "Super Admin" role all permissions
    // This works in the app by using gate-related functions like auth()->user->can() and @can(), for example inside the policies
    Gate::before(function (User $user) {
        return $user->hasRole('Super Admin') ? true : null;
    });
```

Note: The 'Super Admin' role is not created or pre-configured by default.
Please exercise caution when using this role. 
If it is not required for the application, you can remove the Gate from the boot method.

## Spatie Media Library
[Documentation](https://spatie.be/docs/laravel-medialibrary/v11/introduction)  
[GitHub](https://github.com/spatie/laravel-medialibrary)

This package is used to manage media files and associate media with models.

**Config** file was published to `config/media-library.php`.

**Migration** file was published to `0004_01_01_000000_create_media_table.php`.

**Views** with the rendered HTML components were published to `resources/views/vendor/media-library`.

**Environment** variables were added to `.env` file that are used inside the `media-library.php` and `filesystems.php` configuration files:

```dotenv
MEDIA_DISK=media
MEDIA_DISK_DRIVER=local
MEDIA_DISK_ROOT_PATH=app/public/media
MEDIA_DISK_URL_PATH=storage/media
MEDIA_DISK_VISIBILITY=public
MEDIA_DISK__THROW=false
MEDIA_DISK_REPORT=false
MEDIA_QUEUE=media
MEDIA_PREFIX=library
IMAGE_DRIVER=imagick
FFMPEG_PATH="C:/ffmpeg/bin/ffmpeg.exe"
FFPROBE_PATH="C:/ffmpeg/bin/ffprobe.exe"
```

To associate media with a model, the model must implement the following **interface** and **trait**:

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class YourModel extends Model implements HasMedia
{
    use InteractsWithMedia;
}
```

Extra configuration can be defined inside the model,
registering media conversions for example, in the `User` model:

```php
public function registerMediaConversions(?BaseMedia $media = null): void
{
    $this->registerStandardMediaConversions(['avatar', 'images', 'videos', 'documents']);
}
```

The `registerStandardMediaConversions` method is provided by the `HasMediaConversions` trait,
which defines the `preview` (400×400) and `thumb` (40×40) conversions, queued on the `media` queue,
with PDF page extraction and video frame extraction. It also defines a queued responsive WebP conversion
for the `cover`, `images`, and `content` collections. The `HasStandardMediaCollections` trait
registers the standard media collections (`avatar`, `images`, `videos`, `documents`) with responsive images.

A new **Media** model was created inside the `app/models` folder that extends the default SpatieMedia model.
This allows you to add your own methods, add relationships and so on.

```php
namespace App\Models;

use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    //
}
```

### Image conversions

The media library has built-in support to convert images.
To generate conversions of other media types – most notably PDFs and videos – the medialibrary uses image generators to create a derived image file of the media.

The PDF generator requires Imagick, Ghostscript, and Spatie PDF to Image.

The Video generator requires the PHP-FFMpeg package (in composer.json).

More info and documentation is available [here](https://spatie.be/docs/laravel-medialibrary/v11/converting-other-file-types/using-image-generators).

An artisan command is available to generate missing responsive images:

```bash
php artisan media:generate-missing-responsive-images
```

### Filament Plugin

The **Filament plug-in** for uploading media from the admin panel to Spatie Media Library is also installed in composer.json.
Plugin documentation is available [here](https://filamentphp.com/plugins/filament-spatie-media-library).

```composer
"filament/spatie-laravel-media-library-plugin": "^5.7",
```

### Filament RichEditor implementation

https://filamentphp.com/plugins/filament-spatie-media-library#using-media-library-for-rich-editor-file-attachments

We use media library to store file attachments from the rich editor.
To do this, we register a rich content attribute on the model,
similar to how a media library collection is registered.

Calling fileAttachmentProvider() on the attribute registration,
passing in a SpatieMediaLibraryFileAttachmentProvider::make() object.

The `content` media collection is registered via the `HasStandardMediaCollections` trait.

```php
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;

class Post extends Model implements HasRichContent
{
    use InteractsWithRichContent;

    public function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->fileAttachmentProvider(SpatieMediaLibraryFileAttachmentProvider::make());
    }
}
```

### Rich Content Media Synchronization & Alt Text Management

When using Filament's RichEditor with media library file attachments, we manage two key synchronization directions:
1. **Alt Text Synchronization**: Copying the alt text entered in the upload/attach modal into the Spatie Media Library `media.name` column when a model is saved.
2. **Reverse Image Deletion**: Automatically removing the corresponding `<img>` tag from the model's rich content attributes when a media record in the `content` collection is deleted.

To achieve this cleanly and reusable across models, we implemented a robust architecture consisting of a pure HTML parser support class, dedicated Action classes, observer traits, and model observers.

#### 1. `RichContentImages` Support Class

Located at `app/Support/RichContent/RichContentImages.php`, this class provides pure, side-effect-free helper methods:
- `altByUuid()`: Parses HTML content using `DOMDocument`, extracts all `<img>` tags with valid media UUIDs (`data-id`), and maps their UUIDs to their alt text.
- `removeImageByUuid()`: Safely removes any rich-content `<img>` tag matching the given media UUID and cleans up any empty wrapping `<p>` tags.

#### 2. Actions (`SyncImageAltToMediaName` & `RemoveImageFromRichContent`)

- **`SyncImageAltToMediaName`** (`app/Actions/RichContent/SyncImageAltToMediaName.php`): Accepts any model implementing `HasMedia` and rich-content attributes to scan, syncing image alt text to matching `Media` record names.
- **`RemoveImageFromRichContent`** (`app/Actions/RichContent/RemoveImageFromRichContent.php`): Accepts a model and a media UUID, removing the matching image tag from the model's rich content attributes using `RichContentImages::removeImageByUuid()` and saving quietly.

#### 3. `SyncsRichContentImageAltText` Trait

Located at `app/Observers/Traits/SyncsRichContentImageAltText.php`, this trait provides a helper method `syncImageAltToMediaName()` for observers to easily invoke the alt sync action.

#### 4. Observers (`PostObserver` & `MediaObserver`)

- **`PostObserver`**: Uses `SyncsRichContentImageAltText` to sync image alt texts inside the `saved()` event handler.
- **`MediaObserver`**: Listens to the `deleted` event on media records. When a media item belonging to the `content` collection is deleted, it resolves the parent model and invokes `RemoveImageFromRichContent`.

For models with multiple rich-content fields, pass an array of attribute names:

```php
$this->syncImageAltToMediaName($recipe, ['content', 'method']);
```

## Spatie Tags
[Documentation](https://spatie.be/docs/laravel-tags/v4/introduction)  
[GitHub](https://github.com/spatie/laravel-tags)

This package allows you to add tags to models. Laravel Tags comes ready to use.
It offers built-in support for translating tags, multiple tag types, and sorting options.

The **Config** file is published in `config/tags.php`.

The **Migration** file is published in `0005_01_01_000000_create_tag_tables.php`.

To add tags to an Eloquent model, we have added the `HasTags` trait to it:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class Post extends Model
{
    use HasTags;
    
    // ...
}
```

We have extended the standard Spatie Tag model to create our own **Tag** model in the `app/models` folder.
This helps the Filament CMS to manage tags, use SoftDeletes and log activities.

To make this work, the configuration file `tags.php` has been modified to use our own Tag model:

```php 
'tag_model' => \App\Models\Tag::class,
```

The official Filament Plugin is also installed via composer:
https://filamentphp.com/plugins/filament-spatie-tags

## Spatie Eloquent Sortable
[GitHub](https://github.com/spatie/eloquent-sortable)

This package provides a trait that allows an Eloquent model to be sorted.
The package also provides a query scope to retrieve all records in the correct order.

**Config** file was published to `config/eloquent-sortable.php`.

The **trait** `SortableTrait` has been added and the **interface** `Sortable` has been implemented
in models such as Message, Role, User, …, to enable sorting.

A custom **trait**, `SortableOnUpdate`, has also been added,
so that records are re-sorted when the 'order_column' for a single record is changed,
or when a new record is created.

```php
use App\Traits\SortableOnUpdate;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Post extends Model implements Sortable
{
    use SortableOnUpdate;
    use SortableTrait;

    // ...
}
```

The Media model can also be sorted, but the trait has been implemented slightly differently,
to ensure compatibility with the `Media::scopeOrdered` method from the Spatie Media Library.

The `scopeOrdered` method is overridden in the model to return a Builder with a `direction` parameter:

```php
use Illuminate\Database\Eloquent\Builder;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia implements Sortable
{
    use SortableTrait {
        scopeOrdered as traitScopeOrdered;
    }

    public function scopeOrdered($query, string $direction = 'asc'): Builder
    {
        return $this->traitScopeOrdered($query, $direction);
    }
}
```

All sortable models have an 'order_column' in the corresponding database migration file:

```php
$table->unsignedInteger('order_column')->nullable()->index();
```

For pages containing Filament lists, there is a **trait** called `HasOrderTableDefaults`,
which can be used to override the `reorderTable` method, so that the `updated_at` column is preserved
and is not altered during reordering.

Sortable models can be reordered on the list page of the Filament admin panel
by clicking the button at the top left of the table (up/down arrow).

## Spatie Settings
[GitHub](https://github.com/spatie/laravel-settings)

This package allows us to store settings in the database and use them throughout the application.

**Config** file was published to `config/settings.php`.

**Migration** file was published to `0001_01_01_000006_create_settings_table.php`.

**Classes** for settings are placed inside the `app/Settings` folder and must extend the `Spatie\LaravelSettings\Settings` class.

```php
namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public static function group(): string
    {
        return 'general';
    }
}
```

We can add typed properties to the settings class and also make them nullable:

```php
public ?string $google_maps_api_key;
```

You can generate a new settings class using this artisan command.
Before you do, please check if the setting_class_path is correctly set.
You can also specify a path option, which is optional.

```bash
php artisan make:setting GeneralSettings --group=general 
```

Each settings class must be registered inside the `settings.php` config file so it can be loaded by Laravel.

```php
'settings' => [
    GeneralSettings::class,
    TermsSettings::class,
],
```

Default values are set in migration files in the `database/settings` folder for each different setting group.

```php
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.contact_name', config('stickler.contact_details.name'));
        $this->migrator->add('general.contact_address', config('stickler.contact_details.address'));
        $this->migrator->add('general.contact_city', config('stickler.contact_details.city'));
        $this->migrator->add('general.contact_country', config('stickler.contact_details.country'));
        $this->migrator->add('general.contact_company_name', config('stickler.contact_details.company_name'));
        $this->migrator->add('general.contact_company_number', config('stickler.contact_details.company_number'));
        $this->migrator->add('general.contact_email', config('stickler.contact_details.email'));
        $this->migrator->add('general.contact_phone', config('stickler.contact_details.phone'));
        $this->migrator->add('general.social_facebook', config('stickler.social_links.facebook'));
        $this->migrator->add('general.social_instagram', config('stickler.social_links.instagram'));
        $this->migrator->add('general.social_linkedin', config('stickler.social_links.linkedin'));
    }
};
```

**Environment** variables were added to `.env` file that are used inside the configuration file:

```dotenv
SETTINGS_CACHE_ENABLED=true
SETTINGS_CACHE_MEMO=true
```

The official **Filament Plugin** is also installed via composer:  
https://filamentphp.com/plugins/filament-spatie-settings  
https://github.com/filamentphp/spatie-laravel-settings-plugin

All setting Pages are created inside the `app\Filament\Pages\Spatie\Settings` folder.

A custom trait `HasSettingsPageDefaults` was created and is used by all settings Pages

## Spatie Activity Log
[Documentation](https://spatie.be/docs/laravel-activitylog/v5/introduction)  
[GitHub](https://github.com/spatie/laravel-activitylog)

This package offers user-friendly features for recording **user activities** within the app.
It also automatically records **model events**.
All activities are stored in the activity_log table.

**Config** file was published to `config/activitylog.php`.

**Migration** file was published to `0001_01_01_000003_create_activity_log_table.php`.

HasActivity **Trait** was added to the User model to make it possible to log its activities.
All other models that need their activities logged use the `Spatie\Activitylog\Traits\LogsActivity` trait.

In the `app/models` folder, an **Activity** model has been created extending the standard Spatie Activity model.
This means it can be used as a Filament resource and that you can add your own methods to it,
such as custom filters or additional data processing.

You can log activities manually by calling the `activity()` helper function:

```php
// Basic logging
activity()->log('Look mum, I logged something');

// Log model activity
activity()
    ->causedBy($userModel)
    ->performedOn($someContentModel)
    ->log('created');
    
// Log model activity (shorter 'by' and 'on' alias)
activity()
    ->by($userModel)
    ->on($someContentModel)
    ->log('created');
```

If you're not using causedBy() or by(), the package will **automatically use the logged-in user**,
so you can do leave it out and write only this shorter code:

```php
// Log activity
activity()->on($someContentModel)->log('created');
```
Another example with setting **a custom event name**:

```php
// Log activity
activity()
    ->causedBy($userModel)
    ->performedOn($someContentModel)
    ->event('created')
    ->log('Created a new content item');

// Short alias version
activity()
    ->on($someContentModel)
    ->event('created')
    ->log('Created a new content item');

// Inside a Filament action method, f.e. afterCreate()
activity()
    ->on($this->record)
    ->event('created')
    ->log('Created a new Post');
```

Set custom properties on the activity log:

```php
activity()
   ->causedBy($userModel)
   ->performedOn($someContentModel)
   ->withProperties(['key' => 'value'])
   ->log('edited');
```

### Logging model events

The package can automatically log events such as when a model is `created`, `updated` and `deleted`.
To make this work, all you need to do is let your model use the
`Spatie\Activitylog\Models\Concerns\LogsActivity` trait.

```php
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

class Post extends Model
{
    use LogsActivity;
}
```

### CausesActivity and HasActivity traits

The package ships with a `Spatie\Activitylog\Models\Concerns\CausesActivity` trait that can be added to a (User) model.
Since User both causes and logs activities (common for User models), we used the **`HasActivity`** trait instead.
This combines `LogsActivity` and `CausesActivity` and provides these three relationships:

* `activities()` returns activities where this model is the subject (alias for `activitiesAsSubject()`)
* `activitiesAsSubject()` returns activities where this model is the subject
* `activitiesAsCauser()` returns activities where this model is the causer

You can retrieve all the current user's activities like this:

```php
Auth::user()->activitiesAsCauser;
```

### LogsTrashedActivity trait

A custom `LogsTrashedActivity` trait was created to change the default event name
from Spatie Activitylog to 'trashed' when soft deleting.
It keeps the event name 'deleted' when removing a model permanently.

### Cleaning up old activity logs

After using the package for a while, you might have recorded a lot of activity.
This package provides an artisan command `activitylog:clean` to clean the log.
Running this command will result in the deletion of all recorded activity
that is older than the number of days specified in the `clean_after_days` key of the config file.

```bash
php artisan activitylog:clean
```

A schedule command is registered inside `console.php` to run daily

```php
/**
 * Spatie Activity log cleanup
 * 'clean_after_days' is defined in config/activitylog.php
 */
Schedule::command('activitylog:clean --force')
    ->daily()->at('06:03');
```

## Spatie Sitemap
[Documentation](https://spatie.be/docs/laravel-sitemap/v8)  
[GitHub](https://github.com/spatie/laravel-sitemap)

This package can generate a sitemap without you having to add URLs to it manually. 
This works by crawling your entire site.

**Config** file was published to `config/sitemap.php`.

An Artisan command is available at `app/Console/Commands/GenerateSitemap.php` for generating the sitemap.

A queued `GenerateSitemapJob` runs the sitemap regeneration action. The admin controller dispatches this job from the dashboard widget, and publication changes to posts, pages, and projects also dispatch it through their observers.

The admin route is a `POST` endpoint protected by the `edit settings` permission. The dashboard widget submits a CSRF-protected form rather than linking to a mutating `GET` URL.

A sitemap link is also added in the head section of the app layout:

```php
<link rel="sitemap" type="application/xml" title="Sitemap" href="{{ Storage::url('sitemap.xml') }}">
```

## Spatie Health
[Documentation](https://spatie.be/docs/laravel-health/v1/introduction)  
[GitHub](https://github.com/spatie/laravel-health)

Using this package, we can monitor the health of the application.

**Config** file was published to `config/health.php`.

**Migration** file was published to `0001_01_01_000005_create_health_tables.php`.

**HealthServiceProvider** was created inside the `app/Providers/Spatie` folder to register the required health checks.

**Schedule** health checks to run hourly, defined inside `routes/console.php`:

```php
/**
 * Spatie health checks every hour.
 */
Schedule::command(RunHealthChecksCommand::class)
    ->hourlyAt(1);
// Dispatch a very light job on the queue you wish to monitor.
Schedule::command(DispatchQueueCheckJobsCommand::class)
    ->hourlyAt(0);
// Set the heartbeat for the schedule health check.
Schedule::command(ScheduleCheckHeartbeatCommand::class)
    ->hourlyAt(0);
// Prune the health check results daily and only keep history of the last 7 days (defined in config health.php)
Schedule::command('model:prune', ['--model' => [HealthCheckResultHistoryItem::class]])
    ->daily()->at('06:04');
```

A Filament `Health` page was created inside `app/Filament/Pages/Spatie/` to display the health check results.
This page uses the `HealthStatusIndicator` component with corresponding view in `resources/views/components/health-status-indicator.blade.php`.

A custom HealthCheckAction was created inside `app/Actions/HealthCheckAction.php` to run the health check manually.

The `RunHealthChecksCommand::class` is registered inside `bootstrap/app.php`.

## Spatie Backup
[Documentation](https://spatie.be/docs/laravel-backup/v10/introduction)  
[GitHub](https://github.com/spatie/laravel-backup)

This package is used to create backups of the application.
Backups are zipfiles that contain all the folders we specify, along with a dump of the database.

**Config** file was published to `config/backup.php` and a new 'backups' disk is configured inside the `config/filesystems.php` file.

**Translation** files were published to `lang/vendor/backup`.

**Environment** variables were added to `.env` file that are used inside the `backup.php` and `filesystems.php` configuration files:

```dotenv
BACKUP_ARCHIVE_PASSWORD="password"
BACKUP_DISK=backups
BACKUP_DISK_DRIVER=local
BACKUP_DISK_ROOT_PATH=app/private/backups
BACKUP_DISK_URL_PATH=storage/backups
BACKUP_DISK_VISIBILITY=private
BACKUP_DISK__THROW=false
BACKUP_DISK_REPORT=false
```

A **Filament page** inside **Filament/Pages/Spatie** was created for creating and managing backups from the admin panel,  
Together with the **BackupService** for handling data.

A **BackupServiceProvider** was created to handle the backup event listening.

**Permissions** were added to access, download, create, delete backups inside the `PermissionSeeder.php` file.

A scheduled job was added inside the `routes/console.php` file to automatically create new backups and clean up old backups (daily).

```php
/**
 * Spatie backups
 */
Schedule::command('backup:clean')
    ->daily()->at('06:00');
Schedule::command('backup:run --only-db --filename=db-'.app()->environment().'-'.date(config('backup.backup.database_dump_file_timestamp_format')).'.zip')
    ->daily()->at('06:01');
Schedule::command('backup:run --only-files --filename=files-'.app()->environment().'-'.date(config('backup.backup.database_dump_file_timestamp_format')).'.zip')
    ->weeklyOn(Illuminate\Console\Scheduling\Schedule::SUNDAY, '06:02');
```

## Spatie Opening Hours

[Documentation](https://filamentphp.com/plugins/la-boite-a-code-business-hours)  
[GitHub](https://github.com/spatie/opening-hours)

This package provides a data structure for opening hours, with support for weekly schedules, exceptional dates, and vacation periods.

The `OpeningHours` class is used throughout the application to check if a business is open at a given time, retrieve hours for a specific day, and format time ranges.

## Filament Business Hours Plugin

[GitHub](https://github.com/La-boite-a-code/Filament-Business-Hours)

This Filament plugin provides a visual interface for managing opening hours in the admin panel.
It stores opening hours data in the `business_hours` table and uses `spatie/opening-hours` under the hood.

**Config** file was published to `config/filament-business-hours.php`.

**Migration** file was published to `0001_01_03_000000_create_business_hours_table.php`.

**Translation** files were published to `lang/vendor/filament-business-hours/`.

The plugin is registered in `app/Providers/Filament/AdminPanelProvider.php`:

```php
use LaBoiteACode\FilamentBusinessHours\FilamentBusinessHoursPlugin;

->plugins([
    // ... other plugins
    FilamentBusinessHoursPlugin::make()
        ->navigationGroup(__('Settings'))
        ->navigationSort(9200)
        ->canAccessUsing(fn (): bool => auth()->user()?->can('edit settings') ?? false),
]);
```

A custom **seeder** `BusinessHoursSeeder.php` was created to seed default business hours (Monday–Friday, 08:00–12:00 and 13:00–18:00):

```php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessHoursSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('business_hours')->upsert([
            [
                'key' => 'default',
                'timezone' => null,
                'overflow' => false,
                'hours' => json_encode([
                    'monday' => ['08:00-12:00', '13:00-18:00'],
                    'tuesday' => ['08:00-12:00', '13:00-18:00'],
                    'wednesday' => ['08:00-12:00', '13:00-18:00'],
                    'thursday' => ['08:00-12:00', '13:00-18:00'],
                    'friday' => ['08:00-12:00', '13:00-18:00'],
                    'saturday' => [],
                    'sunday' => [],
                ]),
                'exceptions' => json_encode([]),
            ],
        ], ['key']);
    }
}
```

### Displaying Business Hours on the Contact Page

The `ContactController` fetches opening hours from the database and passes them to the view:

```php
use LaBoiteACode\FilamentBusinessHours\Models\BusinessHours;
use LaBoiteACode\FilamentBusinessHours\Support\Weekdays;

$openingHours = BusinessHours::openingHours(config('filament-business-hours.record', 'default'));

return view('public.contact', [
    'openingHours' => $openingHours,
    'weekdays' => Weekdays::DAYS,
]);
```

The contact view (`resources/views/public/contact.blade.php`) displays a weekly hours table with:
- Open/closed status indicator (green/red dot)
- Today's row highlighted with a "Today" badge
- Translated day names using the `filament-business-hours` translation keys
- "Closed" displayed for days with no hours

## Spatie Honeypot
[GitHub](https://github.com/spatie/laravel-honeypot)

This package is used for preventing spam submitted through forms.

**Config** file is published in `config/honeypot.php`.

**View** file is published in `resources/views/vendor/honeypot/honeypotFormFields.blade.php`.

**Environment** variables are added to `.env` file and used inside the config file, with default values:

```dotenv
HONEYPOT_ENABLED=true
HONEYPOT_NAME="first_name"
HONEYPOT_RANDOMIZE=true
HONEYPOT_VALID_FROM_TIMESTAMP=true
HONEYPOT_VALID_FROM="valid_from"
HONEYPOT_SECONDS=1
```

Add the x-honeypot Blade component to any form you wish to protect:

```bladehtml
<!--suppress CheckEmptyScriptTag -->
<form action="{{ route('contact.store') }}" method="POST">
    <x-honeypot />
</form>
```

Next, you must use the `Spatie\Honeypot\ProtectAgainstSpam` middleware in the route that handles the form submission. 
This middleware will intercept any request that submits a non-empty value for the `HONEYPOT_NAME` env key used inside the config file.

It will also intercept the request if it is submitted faster than the encrypted timestamp that the package generated in valid_from_timestamp:

```php
use App\Http\Controllers\Public\ContactController;
use Illuminate\Support\Facades\Route;
use Spatie\Honeypot\ProtectAgainstSpam;

Route::post('/contact', [ContactController::class, 'store'])
    ->name('contact.store')
    ->middleware(ProtectAgainstSpam::class);
```

## Spatie Cookie Consent
[GitHub](https://github.com/spatie/laravel-cookie-consent)  
[Blog](https://freek.dev/503-make-your-laravel-app-comply-with-the-crazy-eu-cookie-law)

This package adds a simple, customizable cookie consent message to the website. 
When the site loads, the banner appears and lets users consent to cookies. 
Once consent is given, the banner hides and stays hidden.

**Config** file is published in `config/cookie-consent.php`.

**View** files are published in `resources/views/vendor/cookie-consent`.

**Translation** files were published to `lang/vendor/cookie-consent`.

**Environment** variable is added to `.env` file and used inside the config file, with default value:

```dotenv
COOKIE_CONSENT_ENABLED=true
```

Inside the `app.blade.php` layout file the cookie consent banner is included:

```php
@include('cookie-consent::index')
```

## Spatie Response Cache
[Documentation](https://spatie.be/docs/laravel-responsecache/v8)   
[GitHub](https://github.com/spatie/laravel-responsecache)  

The first time a request comes in, the package will save the response before sending it to the user. 
When the same request comes in again, the cached response is returned without going through the entire application. 
This will greatly speed up your app.

By default, the package will now cache all successful GET requests that return text based content (such as HTML and JSON) for a week.

**Config** file is published in `config/responsecache.php`.

Inside the `bootstrap/app.php` file the CacheResponse middleware and the DoNotCacheResponse alias are added:

```php
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;

->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        CacheResponse::class,
    ]);

    $middleware->alias([
        'doNotCacheResponse' => DoNotCacheResponse::class,
    ]);
})
```

The `ContactController` does not use the `#[NoCache]` attribute.
To prevent caching on specific routes, you can use the `#[NoCache]` attribute on controller methods
or apply the `doNotCacheResponse` middleware alias to specific routes.

A custom `ClearResponseCacheAction` was created inside `app/Filament/Actions/Spatie/ClearResponseCacheAction.php` to clear the response cache manually.  
This action is registered as a header action on the Filament `Health` page (`app/Filament/Pages/Spatie/Health.php`).

### Clearing the cache when content changes (FlushesResponseCache)

Cached responses are kept for a week, so the cache must be cleared whenever public content changes.  
A custom trait `FlushesResponseCache` was created inside `app/Observers/Traits/FlushesResponseCache.php`.
It clears the response cache when a model is saved (created, updated or restored) or deleted:

```php
use Spatie\ResponseCache\Facades\ResponseCache;

use function Illuminate\Support\defer;

trait FlushesResponseCache
{
    public function saved(Model $model): void
    {
        $this->flushResponseCache();
    }

    public function deleted(Model $model): void
    {
        $this->flushResponseCache();
    }

    protected function flushResponseCache(): void
    {
        if (! config('responsecache.enabled')) {
            return;
        }

        defer(fn () => ResponseCache::clear(), 'flush-response-cache', always: true);
    }
}
```

- Nothing happens when response caching is disabled (`RESPONSE_CACHE_ENABLED=false`).
- The cache is cleared with Laravel's `defer()`, after the response has been sent (and after the database changes are committed).
- The deferred callback is named, so the cache is cleared only **once** per request, job or command, no matter how many models are saved.
- `always: true` makes sure the cache is still cleared when the request ends with an error after the data was saved.

The trait is used by the observers of all content that is shown on the public website:

| Observer                                         | Clears the cache on                                                      |
|--------------------------------------------------|--------------------------------------------------------------------------|
| `app/Observers/PostObserver.php`                 | `saved`, `deleted`                                                       |
| `app/Observers/PageObserver.php`                 | `saved`, `deleted`                                                       |
| `app/Observers/ProjectObserver.php`              | `saved`, `deleted`                                                       |
| `app/Observers/TagObserver.php`                  | `saved`, `deleted`                                                       |
| `app/Observers/MediaObserver.php`                | `saved`, `deleted` (for every media collection)                          |
| `app/Observers/Spatie/BusinessHoursObserver.php` | `saved`, `deleted` (business hours are shown on the cached contact page) |

Observers that already have their own `saved()` or `deleted()` method (that method overrides the one from the trait)
call `$this->flushResponseCache();` themselves inside that method:

```php
use App\Observers\Traits\FlushesResponseCache;

class PostObserver
{
    use FlushesResponseCache;

    public function saved(Post $post): void
    {
        $this->flushResponseCache();

        // ...
    }
}
```

When you add a new model that is shown on the public website, add `use FlushesResponseCache;` to its observer.

> [!NOTE]
> Spatie Settings are not Eloquent models, so changing settings does not clear the response cache automatically.
> Use the `ClearResponseCacheAction` on the Health page after changing settings that are shown on the website.

### Clearing the cache for scheduled content (ClearScheduledContentCacheCommand)

Posts, pages and projects can be scheduled with a future `published_at` date.
When that moment passes no model event is fired, so the `FlushesResponseCache` trait cannot clear the cache.
A custom command `ClearScheduledContentCacheCommand` was created inside `app/Console/Commands/ClearScheduledContentCacheCommand.php` to handle this:

```bash
php artisan content:clear-scheduled-cache
```

How it works:

1. The command reads the time of its previous check from the default cache store (key `scheduled-content:last-checked-at`).
2. It checks if any `Post`, `Page` or `Project` has a `published_at` between the previous check and now.
3. If so, the response cache is cleared with `ResponseCache::clear()`.
4. The current time is stored as the new last-checked time.

- Because the command checks the whole period since its previous run, no content is missed when the scheduler was down for a while.
- When no previous check is stored (first run, or after `php artisan cache:clear`), the response cache is cleared once to be safe.
- When response caching is disabled, the cache is never cleared, but the last-checked time is still updated.
- Soft-deleted content is ignored.

The command uses the `becamePublishedBetween` scope that was added to the `HasPublishedScope` trait (`app/Traits/HasPublishedScope.php`),
which is used by the `Post`, `Page` and `Project` models:

```php
public function scopeBecamePublishedBetween(Builder $query, CarbonInterface $from, CarbonInterface $until): Builder
{
    return $query
        ->where('published_at', '>', $from)
        ->where('published_at', '<=', $until);
}
```

The command is scheduled every hour at minute zero inside `routes/console.php`:

```php
/**
 * Clear the response cache when scheduled content becomes public.
 */
Schedule::command('content:clear-scheduled-cache')
    ->hourlyAt(0)
    ->withoutOverlapping()
    ->onOneServer();
```

- Scheduled content becomes visible on the website **at most one hour** after its `published_at` date.
- The Laravel scheduler must be running (a cron job for `php artisan schedule:run`, or `php artisan schedule:work` locally).
- `onOneServer()` and `withoutOverlapping()` need a cache store that supports atomic locks (e.g. `database` or `redis`).
  When the application runs on multiple servers, all servers must share the same cache store.

More configuration is possible, for now this project is using the default settings.

## Spatie HTTP Logger
[GitHub](https://github.com/spatie/laravel-http-logger)
[Blog](https://freek.dev/882-a-laravel-package-to-log-http-requests)

This package adds a middleware which can log incoming requests to the default log.
If anything goes wrong during a user's request, you'll still be able to access the original request data sent by that user.
Log entries can be viewed in OPcodes Log Viewer (see documentation below).

**Config** file is published in `config/http-logger.php`.

**Environment** variables `'HTTP_LOGGER_ENABLED'` and `'HTTP_LOGGER_CHANNEL'`
are added to `.env` file and used inside the config file, with default values:

```dotenv
HTTP_LOGGER_ENABLED=true
HTTP_LOGGER_CHANNEL=http
```

Pre-existing environment variables for the logging settings inside config files, with default values:

```dotenv
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
```

We added a custom Log Channel to the log configuration in `config/logging.php` to log the HTTP requests:

```php
'channels' => [
    // ...
    
    'http' => [
        'driver' => 'single',
        'path' => storage_path('logs/http-logger.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'replace_placeholders' => true,
    ],
    
    // ...
],
```

**Middleware** is provided by this package and can be added as global middleware inside the `bootstrap/app.php` file:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Spatie\HttpLogger\Middlewares\HttpLogger::class);
})
```

For this project, we added this middleware to the contact form submission route inside `routes/web.php`:
```php
use Spatie\HttpLogger\Middlewares\HttpLogger;

Route::post('/contact', [ContactController::class, 'store'])
    ->name('contact.store')
    ->middleware([HttpLogger::class, ProtectAgainstSpam::class, 'throttle:3,1']);
```

The config file `config/http-logger.php` uses `LogNonGetRequests` by default, which logs all non-GET requests.

## OPcodes Log Viewer
[Documentation](https://log-viewer.opcodes.io/)  
[GitHub](https://github.com/opcodesio/log-viewer)

Log Viewer is a package allowing you to easily read, search and manage your Laravel logs.  
You will no longer need to read the raw log files trying to find what you're looking for.

**Config** file was published to `config/log-viewer.php`.

**Environment** variables were added to `.env` file that are used inside the config file, with default values:

```dotenv
LOG_VIEWER_ENABLED=true
LOG_VIEWER_API_ONLY=false
```

The Log Viewer can be accessed at URI [admin/log-viewer](https://stickler.test/admin/log-viewer).  
Access is restricted by default for users with the `access logs` permission.

Inside the default `AuthorizeLogViewer` middleware from log-viewer is an existence check for `Gate::has('viewLogViewer')`.  
We defined this `viewLogViewer` **Gate** inside the boot method of the `AccessServiceProvider` (`app/Providers/Spatie/AccessServiceProvider.php`) to check for the permission:

```php
namespace App\Providers\Spatie;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AccessServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Grant access to the Log Viewer in production environment when the user has the "access logs" permission
        // https://log-viewer.opcodes.io/docs/3.x/configuration/access-to-log-viewer#via-viewlogviewer-gate
        Gate::define('viewLogViewer', function (User $user) {
            return $user->can('access logs');
        });

        Gate::define('downloadLogFile', function (User $user) {
            return $user->can('download logs');
        });

        Gate::define('deleteLogFile', function (User $user) {
            return $user->can('delete logs');
        });
    }
}
```

We can also restrict certain additional permissions,
such as granting access to individual log files or downloading and deleting log files:

```php
Gate::define('downloadLogFile', function (User $user) {
    return $user->can('download logs');
});

Gate::define('deleteLogFile', function (User $user) {
    return $user->can('delete logs');
});
```

More info is available in the [documentation](https://log-viewer.opcodes.io/docs/3.x/configuration/access-to-log-files).

### Front-end assets
Front-end assets can be published to `public/vendor/log-viewer` folder, using the artisan command:

```
php artisan log-viewer:publish
```

**Note**: Publishing assets is **no longer required**.
Assets are now served directly from the vendor directory.
This option only applies if you have already published older assets using `php artisan log-viewer:publish`.  
**This option will be removed in the next major version** of the package.

## Jaybizzle Crawler Detect
[GitHub](https://github.com/JayBizzle/Crawler-Detect)

This package detects bots and crawlers by their User-Agent string. It ships a maintained list of bot patterns and is used by the page view tracker to flag bot visits.

The package is required in `composer.json` (`"jaybizzle/crawler-detect": "^1.4"`).

The `TrackPageView` middleware (`app/Http/Middleware/TrackPageView.php`) uses the `Jaybizzle\CrawlerDetect\CrawlerDetect` class:

```php
use Jaybizzle\CrawlerDetect\CrawlerDetect;

$isBot = (new CrawlerDetect)->isCrawler($request->userAgent());
```

The result is stored in the `is_bot` column of the `page_views` table. The middleware also skips requests that have an empty User-Agent or a browser prefetch header (`Purpose: prefetch` or `Sec-Purpose: prefetch`).

No User-Agent string is stored, only the boolean flag. Bot visits are excluded by default from the `PageViewsWidget` and the `PageViewResource`.

Limitation: the detection only catches bots that identify themselves in the User-Agent. Scrapers with a browser User-Agent are still counted as human visits.

Check the installed version with:

```bash
composer show jaybizzle/crawler-detect
```

## Other Composer Dependencies

The following direct Composer dependencies are part of the starter kit but do not require a dedicated implementation guide. They are listed here so this document remains aligned with `composer.json`.

### Laravel and Filament foundation

- **Laravel Framework** (`laravel/framework`) — the application framework.
- **Laravel Tinker** (`laravel/tinker`) — the interactive Laravel REPL.
- **Filament** (`filament/filament`) — the admin panel framework; see [FILAMENT.md](FILAMENT.md).
- **Filament Spatie Media Library Plugin** (`filament/spatie-laravel-media-library-plugin`) — Filament integration for media uploads; see [FILAMENT.md](FILAMENT.md).
- **Filament Spatie Settings Plugin** (`filament/spatie-laravel-settings-plugin`) — Filament integration for settings; see [FILAMENT.md](FILAMENT.md).
- **Filament Spatie Tags Plugin** (`filament/spatie-laravel-tags-plugin`) — Filament integration for tags; see [FILAMENT.md](FILAMENT.md).
- **Resized Column** (`asmit/resized-column`) — resizable Filament table columns; see [FILAMENT.md](FILAMENT.md).
- **Business Hours** (`laboiteacode/filament-business-hours`) — business-hours management in Filament; see [FILAMENT.md](FILAMENT.md).

### Storage and media support

- **Flysystem AWS S3 adapter** (`league/flysystem-aws-s3-v3`) — S3-compatible filesystem storage.
- **PHP-FFMpeg** (`php-ffmpeg/php-ffmpeg`) — audio and video processing support.
- **Spatie PDF to Image** (`spatie/pdf-to-image`) — PDF preview/image conversion support.

### Security and health

- **Spatie Security Advisories Health Check** (`spatie/security-advisories-health-check`) — reports vulnerable Composer dependencies through the health checks.

### Development and testing

- **Laravel Pail** (`laravel/pail`) — readable application log output in the terminal.
- **Laravel PAO** (`laravel/pao`) — agent-friendly output for Laravel testing tools.
- **Pest** (`pestphp/pest`) and its Laravel and Livewire plugins — the application testing framework and integrations.
- **Faker** (`fakerphp/faker`) — test data generation.
- **Mockery** (`mockery/mockery`) — test doubles and mocks.
- **Collision** (`nunomaduro/collision`) — improved CLI exception output.
- **Laravel Pint** (`laravel/pint`) — PHP code formatting.

Package versions are defined by the constraints in `composer.json`; use `composer show --direct` to inspect the installed versions.
