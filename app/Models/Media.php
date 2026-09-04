<?php

/** @noinspection PhpMissingReturnTypeInspection, PhpUnused */

namespace App\Models;

use App\Observers\MediaObserver;
use App\Policies\MediaPolicy;
use App\Traits\SortableOnUpdate;
use DateTime;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Spatie\Tags\HasTags;

/**
 * @property int $id
 * @property string $model_type
 * @property int $model_id
 * @property string $uuid
 * @property string $collection_name
 * @property string $name
 * @property string $file_name
 * @property string $mime_type
 * @property string $disk
 * @property string $conversions_disk
 * @property int $size
 * @property string $manipulations
 * @property string $custom_properties
 * @property string $generated_conversions
 * @property string $responsive_images
 * @property int $order_column
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property Attribute $title
 * @property Model $model
 * @property BelongsTo $creator
 * @property BelongsTo $updater
 *
 * @method static creator()
 * @method static updater()
 * @method static distinct()
 * @method static findOrFail(int $id)
 * @method static create(array $array)
 * @method static find($mediaId)
 */
#[ObservedBy(MediaObserver::class)]
#[UsePolicy(MediaPolicy::class)]
class Media extends BaseMedia implements Sortable
{
    use HasTags;
    use LogsActivity;
    use SortableOnUpdate;
    use SortableTrait {
        scopeOrdered as traitScopeOrdered;
    }

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
    | Spatie Media Library
    |--------------------------------------------------------------------------
    */

    /**
     * Admin route to the media owner Model edit page
     */
    public function mediaOwnerRoute(): string
    {
        $modelRouteSlug = str_replace('app\\models\\', '', mb_strtolower($this->model_type)).'s';

        return 'filament.admin.resources.'.$modelRouteSlug.'.edit';
    }

    /**
     * Admin title for the media owner Model
     */
    public function mediaOwnerTitle(): string
    {
        return str_replace('App\\Models\\', '', $this->model_type);
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
            ->logExcept(['generated_conversions', 'responsive_images', 'updated_at'])
            ->dontLogIfAttributesChangedOnly([
                'updated_at',
            ])
            ->setDescriptionForEvent(fn (string $eventName) => __('This item has been :event', ['event' => __($eventName)]));
    }

    /*
    |--------------------------------------------------------------------------
    | Spatie Eloquent Sortable
    |--------------------------------------------------------------------------
    */

    /**
     * This function is used for Spatie Media package to be compatible with the Spatie Eloquent Sortable package.
     * The second parameter is the direction of the sorting, default is 'asc' for ascending.
     */
    public function scopeOrdered($query, string $direction = 'asc'): Builder
    {
        return $this->traitScopeOrdered($query, $direction);
    }

    public function buildSortQuery()
    {
        return static::query()
            ->where('model_type', $this->model_type)
            ->where('model_id', $this->model_id)
            ->where('collection_name', $this->collection_name);
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    public function model(): MorphTo
    {
        return $this->morphTo()
            ->withTrashed();
    }

    /**
     * Get the user that created the media.
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
     * Get the user that updated the media.
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
