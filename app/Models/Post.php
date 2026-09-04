<?php

namespace App\Models;

use App\Observers\PostObserver;
use App\Policies\PostPolicy;
use App\Traits\LogsTrashedActivity;
use App\Traits\SortableOnUpdate;
use Database\Factories\PostFactory;
use DateTime;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\Attributes\Sluggable;
use Spatie\Tags\HasTags;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $intro
 * @property string $content
 * @property int $order_column
 * @property DateTime $published_at
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property DateTime|null $deleted_at
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property Attribute $published
 * @property BelongsTo $creator
 * @property BelongsTo $updater
 *
 * @method static creator()
 * @method static updater()
 * @method static create(mixed $validated)
 * @method static forceCreate(array $array)
 * @method static where(string $string, mixed $id)
 */
#[Fillable([
    'slug',
    'title',
    'intro',
    'content',
    'order_column',
    'published_at',
])]
#[Hidden([])]
#[Sluggable(
    from: 'title',
    to: 'slug',
    separator: '-',
    language: 'nl',
    maxLength: 48,
    unique: true,
    onUpdate: false,
)]
#[ObservedBy(PostObserver::class)]
#[UsePolicy(PostPolicy::class)]
class Post extends Model implements HasMedia, HasRichContent, Sortable
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use HasTags;
    use InteractsWithMedia;
    use InteractsWithRichContent;
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'published' => 'boolean',
        ];
    }

    /**
     * Interact with the published_at state.
     * Returns true if the published_at timestamp is set.
     */
    protected function published(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->published_at !== null,
        );
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
    public function registerMediaConversions(?Media $media = null): void
    {
        // The Collection component will show a preview thumbnail for items in the collection it is showing.
        // To generate that thumbnail, we must add a conversion like this one to the model.
        $this->addMediaConversion('preview')
            ->fit(Fit::Contain, 200, 200)
            ->nonQueued()
            ->deferred()
            ->performOnCollections('cover', 'images', 'videos', 'documents', 'content')
            ->pdfPageNumber(1)
            ->extractVideoFrameAtSecond(1);

        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 40, 40)
            ->nonQueued()
            ->deferred()
            ->performOnCollections('cover', 'images', 'videos', 'documents', 'content')
            ->pdfPageNumber(1)
            ->extractVideoFrameAtSecond(1);
    }

    /**
     * Media Collections are used to group media items together.
     * You can define as many collections as needed.
     */
    public function registerMediaCollections(): void
    {
        // Cover image single-file collection
        $this->addMediaCollection('cover')
            ->useDisk(config('media-library.disk_name'))
            ->singleFile()
            ->withResponsiveImages()
            ->useFallbackUrl('/art/placeholders/pietje-200.png')
            ->useFallbackPath(public_path('/art/placeholders/pietje-200.png'));

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

        // Rich content collection
        $this->addMediaCollection('content')
            ->useDisk(config('media-library.disk_name'))
            ->withResponsiveImages();
    }

    /**
     * Rich content
     */
    public function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->fileAttachmentProvider(SpatieMediaLibraryFileAttachmentProvider::make());
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
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->dontLogIfAttributesChangedOnly([
                'updated_at',
            ])
            ->setDescriptionForEvent(fn (string $eventName) => __('This item has been :event', ['event' => __($eventName)]));
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the user that created the post.
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
     * Get the user that updated the post.
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
