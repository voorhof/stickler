<?php

namespace App\Models;

use App\Observers\ProductObserver;
use App\Policies\ProductPolicy;
use App\Traits\HasCategories;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasMediaConversions;
use App\Traits\HasPublishedScope;
use App\Traits\HasStandardActivitylogOptions;
use App\Traits\HasStandardMediaCollections;
use App\Traits\LogsTrashedActivity;
use App\Traits\RenderResponsiveRichContent;
use App\Traits\SortableOnUpdate;
use Database\Factories\ProductFactory;
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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HigherOrderCollectionProxy;
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
 * @property string|null $content
 * @property string|null $sku
 * @property float $price
 * @property float|null $discount_price
 * @property bool $is_discounted
 * @property int $stock_quantity
 * @property string|null $website_title
 * @property string|null $website_url
 * @property int|null $order_column
 * @property DateTime|null $published_at
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property DateTime|null $deleted_at
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property Attribute $published
 * @property BelongsTo $creator
 * @property BelongsTo $updater
 * @property Collection<Category> $categories
 * @property HigherOrderCollectionProxy|mixed $variants
 *
 * @method static creator()
 * @method static updater()
 * @method static create(mixed $validated)
 * @method static forceCreate(array $array)
 * @method static where(string $column, mixed $operator, mixed $value = null)
 * @method static isPublicPublished()
 * @method static first()
 */
#[Fillable([
    'slug',
    'title',
    'intro',
    'content',
    'sku',
    'price',
    'discount_price',
    'is_discounted',
    'stock_quantity',
    'website_title',
    'website_url',
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
#[ObservedBy(ProductObserver::class)]
#[UsePolicy(ProductPolicy::class)]
class Product extends Model implements HasMedia, HasRichContent, Sortable
{
    use HasCategories;
    use HasCreatorAndUpdater;

    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use HasMediaConversions;
    use HasPublishedScope;
    use HasStandardActivitylogOptions;
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
            'is_discounted' => 'boolean',
            'stock_quantity' => 'integer',
            'order_column' => 'integer',
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

    /**
     * Convert price from cents (database) to euros (application).
     */
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value !== null ? $value / 100 : null,
            set: fn ($value) => $value !== null ? (int) round($value * 100) : null,
        );
    }

    /**
     * Convert discount price from cents (database) to euros (application).
     */
    protected function discountPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value !== null ? $value / 100 : null,
            set: fn ($value) => $value !== null ? (int) round($value * 100) : null,
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
        $this->registerStandardMediaConversions(['cover', 'images', 'videos', 'documents']);
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
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get the product variants.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
