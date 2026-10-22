<?php

namespace App\Models;

use App\Observers\UserObserver;
use App\Policies\UserPolicy;
use App\Traits\HasMediaConversions;
use App\Traits\HasStandardMediaCollections;
use App\Traits\LogsTrashedActivity;
use App\Traits\SortableOnUpdate;
use App\Traits\TracksCreatorAndUpdater;
use Database\Factories\UserFactory;
use DateTime;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Models\Concerns\HasActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
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
#[RouteKey('slug')]
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
#[UsePolicy(UserPolicy::class)]
class User extends Authenticatable implements FilamentUser, HasMedia, HasName, MustVerifyEmail, Sortable
{
    use HasActivity;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasMediaConversions;
    use HasRoles;
    use HasStandardMediaCollections {
        HasStandardMediaCollections::registerMediaCollections insteadof InteractsWithMedia;
    }
    use InteractsWithMedia;
    use LogsTrashedActivity;
    use Notifiable;
    use SoftDeletes;
    use SortableOnUpdate;
    use SortableTrait;
    use TracksCreatorAndUpdater;

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
    | Spatie Media Library
    |--------------------------------------------------------------------------
    */

    /**
     * Media conversions are used to generate different versions of a media item.
     * You can define as many conversions as needed.
     */
    public function registerMediaConversions(?BaseMedia $media = null): void
    {
        $this->registerStandardMediaConversions(['avatar', 'images', 'videos', 'documents']);
    }

    /**
     * Get the collection names that should receive the webp responsive image conversion.
     *
     * @return array<string>
     */
    protected function webpCollections(): array
    {
        return ['avatar', 'images'];
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
     * Get the user that created this user.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'created_by_user_id')
            ->withDefault([
                'name' => 'GUEST USER',
            ]);
    }

    /**
     * Get the user that updated this user.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(self::class, 'updated_by_user_id')
            ->withDefault([
                'name' => 'GUEST USER',
            ]);
    }
}
