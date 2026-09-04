<?php

/** @noinspection PhpUnused */

namespace App\Models;

use App\Observers\UserObserver;
use App\Traits\LogsTrashedActivity;
use App\Traits\SortableOnUpdate;
use Database\Factories\UserFactory;
use DateTime;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\HasActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Sluggable\Attributes\Sluggable;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $email
 * @property DateTime|null $email_verified_at
 * @property string $locale
 * @property string $password
 * @property string $remember_token
 * @property int $order_column
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property DateTime|null $deleted_at
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property Attribute $title
 * @property BelongsTo $creator
 * @property BelongsTo $updater
 *
 * @method static creator()
 * @method static updater()
 * @method static forceCreate(array $array)
 */
#[Fillable([
    'slug',
    'name',
    'email',
    'locale',
    'password',
    'order_column',
])]
#[Hidden([
    'password',
    'remember_token',
])]
#[Sluggable(
    from: 'name',
    to: 'slug',
    separator: '-',
    language: 'nl',
    maxLength: 48,
    unique: true,
    onUpdate: false,
)]
#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements FilamentUser, HasMedia, HasName, MustVerifyEmail, Sortable
{
    use HasActivity;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use InteractsWithMedia;
    use LogsTrashedActivity;
    use Notifiable;
    use SoftDeletes;
    use SortableOnUpdate;
    use SortableTrait;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'locale' => 'nl_BE',
        'order_column' => 1,
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts, Accessors and Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locale' => 'string',
            'password' => 'hashed',
        ];
    }

    /**
     * Interact with the user name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? ucwords($value) : null,
            set: fn (?string $value) => $value ? mb_strtolower($value) : null,
        );
    }

    /**
     * Retrieve the user’s role names for use in the activity log.
     */
    public function getRoleNames(): array
    {
        return $this->roles()->pluck('name')->toArray();
    }

    /**
     * Retrieve the user’s preferred location.
     */
    public function preferredLocale(): string
    {
        return $this->locale;
    }

    /**
     * Get title: $this->title
     * Used inside Filament ActivityHistorySection
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn () => "$this->name",
        )->shouldCache();
    }

    /*
    |--------------------------------------------------------------------------
    | Filament User
    |--------------------------------------------------------------------------
    */

    /**
     * Determine if the user can access the given Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->can('access admin');
    }

    /**
     * Get the name attribute of the user
     * to display their name in the app (e.g., on the dashboard welcome widget).
     */
    public function getFilamentName(): string
    {
        return $this->name;
    }

    /*
    |--------------------------------------------------------------------------
    | Spatie Sluggable
    |--------------------------------------------------------------------------
    |
    | The #[Sluggable()] attribute is used to generate slugs for the model.
    | https://spatie.be/docs/laravel-sluggable/v4/introduction
    */

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /*
    |--------------------------------------------------------------------------
    | Spatie Media Library
    |--------------------------------------------------------------------------
    */

    /**
     * Media conversions are used to generate different versions of a media item.
     * You can define as many conversions as needed.
     */
    public function registerMediaConversions(?BaseMedia $media = null): void
    {
        // The Collection component will show a preview thumbnail for items in the collection it is showing.
        // To generate that thumbnail, we must add a conversion like this one to the model.
        $this->addMediaConversion('preview')
            ->fit(Fit::Contain, 200, 200)
            ->nonQueued()
            ->deferred()
            ->performOnCollections('avatar', 'images', 'videos', 'documents')
            ->pdfPageNumber(1)
            ->extractVideoFrameAtSecond(1);

        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 40, 40)
            ->nonQueued()
            ->deferred()
            ->performOnCollections('avatar', 'images', 'videos', 'documents')
            ->pdfPageNumber(1)
            ->extractVideoFrameAtSecond(1);
    }

    /**
     * Media Collections are used to group media items together.
     * You can define as many collections as needed.
     */
    public function registerMediaCollections(): void
    {
        $useFallbackUrl = 'https://ui-avatars.com/api/?name='.(mb_substr($this->name, 0, 1) ?: 'X').'&format=svg&color=FFFFFF&background=0b0809';

        // Avatar single-file collection
        $this->addMediaCollection('avatar')
            ->useDisk(config('media-library.disk_name'))
            ->singleFile()
            ->withResponsiveImages()
            ->useFallbackUrl($useFallbackUrl);

        // Images multi-file collection
        $this->addMediaCollection('images')
            ->useDisk(config('media-library.disk_name'))
            ->withResponsiveImages();

        // Videos multi-file collection
        $this->addMediaCollection('videos')
            ->useDisk(config('media-library.disk_name'));

        // Documents multi-file collection
        $this->addMediaCollection('documents')
            ->useDisk(config('media-library.disk_name'));
    }

    /*
    |--------------------------------------------------------------------------
    | Spatie Activitylog
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept(['password', 'updated_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->dontLogIfAttributesChangedOnly([
                'updated_at',
                'updated_by_user_id',
            ])
            ->setDescriptionForEvent(fn (string $eventName) => __('This item has been :event', ['event' => __($eventName)]));
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the media created by the user.
     */
    public function createdMedia(): HasMany
    {
        return $this->hasMany(Media::class, 'created_by_user_id', 'id')
            ->chaperone('creator');
    }

    /**
     * Get the media updated by the user.
     */
    public function updatedMedia(): HasMany
    {
        return $this->hasMany(Media::class, 'updated_by_user_id', 'id')
            ->chaperone('updater');
    }

    /**
     * Get the messages created by the user.
     */
    public function createdMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'created_by_user_id', 'id')
            ->chaperone('creator');
    }

    /**
     * Get the messages updated by the user.
     */
    public function updatedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'updated_by_user_id', 'id')
            ->chaperone('updater');
    }

    /**
     * Get the posts created by the user.
     */
    public function createdPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'created_by_user_id', 'id')
            ->chaperone('creator');
    }

    /**
     * Get the posts updated by the user.
     */
    public function updatedPosts(): HasMany
    {
        return $this->hasMany(Post::class, 'updated_by_user_id', 'id')
            ->chaperone('updater');
    }

    /**
     * Get the projects created by the user.
     */
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by_user_id', 'id')
            ->chaperone('creator');
    }

    /**
     * Get the projects updated by the user.
     */
    public function updatedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'updated_by_user_id', 'id')
            ->chaperone('updater');
    }

    /**
     * Get the roles created by the user.
     */
    public function createdRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'created_by_user_id', 'id')
            ->chaperone('creator');
    }

    /**
     * Get the roles updated by the user.
     */
    public function updatedRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'updated_by_user_id', 'id')
            ->chaperone('updater');
    }

    /**
     * Get the tags created by the user.
     */
    public function createdTags(): HasMany
    {
        return $this->hasMany(Tag::class, 'created_by_user_id', 'id')
            ->chaperone('creator');
    }

    /**
     * Get the tags updated by the user.
     */
    public function updatedTags(): HasMany
    {
        return $this->hasMany(Tag::class, 'updated_by_user_id', 'id')
            ->chaperone('updater');
    }

    /**
     * Get the users created by the user.
     */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(self::class, 'created_by_user_id', 'id')
            ->chaperone('creator');
    }

    /**
     * Get the users updated by the user.
     */
    public function updatedUsers(): HasMany
    {
        return $this->hasMany(self::class, 'updated_by_user_id', 'id')
            ->chaperone('updater');
    }

    /**
     * Get the user that created the user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by_user_id')
            ->withDefault([
                'name' => 'GUEST USER',
            ]);
    }

    /**
     * Get the user that updated the user.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(self::class, 'updated_by_user_id')
            ->withDefault([
                'name' => 'GUEST USER',
            ]);
    }
}
