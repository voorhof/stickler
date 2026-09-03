<?php

/** @noinspection PhpUnused */

namespace App\Models;

use App\Observers\TagObserver;
use App\Policies\TagPolicy;
use App\Traits\LogsTrashedActivity;
use App\Traits\SortableOnUpdate;
use Database\Factories\TagFactory;
use DateTime;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\Attributes\Sluggable;
use Spatie\Tags\Tag as BaseTag;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $url_slug
 * @property string $type
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
 * @method static create(mixed $validated)
 * @method static forceCreate(array $array)
 */
#[Fillable([
    'name',
    'url_slug',
    'type',
    'order_column',
])]
#[Hidden([])]
#[Sluggable(
    from: 'name',
    to: 'url_slug',
    separator: '-',
    language: 'nl',
    maxLength: 48,
    unique: true,
    onUpdate: false,
)]
#[ObservedBy(TagObserver::class)]
#[UsePolicy(TagPolicy::class)]
class Tag extends BaseTag implements Sortable
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    use LogsActivity;
    use LogsTrashedActivity;
    use SoftDeletes;
    use SortableOnUpdate;
    use SortableTrait;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'order_column' => 1,
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts, Accessors and Mutators
    |--------------------------------------------------------------------------
    */

    /**
     * Get role title: $this->title
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn () => "$this->name",
        )->shouldCache();
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
        return 'url_slug';
    }

    /*
    |--------------------------------------------------------------------------
    | Spatie Activitylog
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->dontLogIfAttributesChangedOnly([
                'updated_at',
            ])
            ->setDescriptionForEvent(fn (string $eventName) => __('This item has been :event', ['event' => __($eventName)]));
    }

    /*
    |--------------------------------------------------------------------------
    | Spatie Translations
    |--------------------------------------------------------------------------
    */

    public static function getLocale(): string
    {
        return 'nl_BE';
    }

    public function getFallbackLocale(): string
    {
        return 'en_US';
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get all the media that are assigned to the tag.
     */
    public function media(): MorphToMany
    {
        return $this->morphedByMany(Media::class, 'taggable')
            ->orderBy('order_column');
    }

    /**
     * Get all the posts that are assigned to the tag.
     */
    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'taggable')
            ->orderBy('order_column');
    }

    /**
     * Get all the projects that are assigned to the tag.
     */
    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'taggable')
            ->orderBy('order_column');
    }

    /**
     * Get the user that created the tag.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id')
            ->withTrashed()
            ->withDefault([
                'name' => 'GUEST USER',
            ]);
    }

    /**
     * Get the user that updated the tag.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id')
            ->withTrashed()
            ->withDefault([
                'name' => 'GUEST USER',
            ]);
    }
}
