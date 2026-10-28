<?php

namespace App\Models;

use App\Observers\PostObserver;
use App\Policies\PostPolicy;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasMediaConversions;
use App\Traits\HasPublishedScope;
use App\Traits\HasStandardActivitylogOptions;
use App\Traits\HasStandardMediaCollections;
use App\Traits\LogsTrashedActivity;
use App\Traits\RenderResponsiveRichContent;
use App\Traits\SortableOnUpdate;
use Database\Factories\PostFactory;
use DateTime;
use Filament\Forms\Components\RichEditor\FileAttachmentProviders\SpatieMediaLibraryFileAttachmentProvider;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
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
 * @method static where(string $column, mixed $operator, mixed $value = null)
 * @method static isPublicPublished()
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
#[RouteKey('slug')]
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
    use HasCreatorAndUpdater;

    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use HasMediaConversions;
    use HasPublishedScope;
    use HasStandardActivitylogOptions {
        HasStandardActivitylogOptions::getActivitylogOptions insteadof LogsActivity;
    }
    use HasStandardMediaCollections {
        HasStandardMediaCollections::registerMediaCollections insteadof InteractsWithMedia;
    }
    use HasTags;
    use InteractsWithMedia;
    use InteractsWithRichContent;
    use LogsActivity;
    use LogsTrashedActivity;
    use RenderResponsiveRichContent;
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
    | Spatie Media Library
    |--------------------------------------------------------------------------
    */

    /**
     * Media conversions are used to generate different versions of a media item.
     * You can define as many conversions as needed.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->registerStandardMediaConversions(['cover', 'images', 'videos', 'documents', 'content']);
    }

    /**
     * Rich content
     */
    public function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->fileAttachmentProvider(SpatieMediaLibraryFileAttachmentProvider::make());
    }
}
