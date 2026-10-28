<?php

namespace App\Models;

use App\Observers\CategoryObserver;
use App\Policies\CategoryPolicy;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasStandardActivitylogOptions;
use App\Traits\LogsTrashedActivity;
use App\Traits\SortableOnUpdate;
use Database\Factories\CategoryFactory;
use DateTime;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\HigherOrderCollectionProxy;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\Attributes\Sluggable;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property bool $for_pages
 * @property bool $for_posts
 * @property bool $for_products
 * @property bool $for_projects
 * @property int $order_column
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property DateTime|null $deleted_at
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property BelongsTo $creator
 * @property BelongsTo $updater
 * @property HigherOrderCollectionProxy|mixed $pages
 * @property HigherOrderCollectionProxy|mixed $posts
 * @property HigherOrderCollectionProxy|mixed $products
 * @property HigherOrderCollectionProxy|mixed $projects
 *
 * @method static creator()
 * @method static updater()
 * @method static create(mixed $validated)
 * @method static forceCreate(array $array)
 */
#[Fillable([
    'slug',
    'title',
    'for_pages',
    'for_posts',
    'for_products',
    'for_projects',
    'order_column',
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
#[ObservedBy(CategoryObserver::class)]
#[UsePolicy(CategoryPolicy::class)]
class Category extends Model implements Sortable
{
    use HasCreatorAndUpdater;

    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasStandardActivitylogOptions {
        HasStandardActivitylogOptions::getActivitylogOptions insteadof LogsActivity;
    }
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
        'for_pages' => false,
        'for_posts' => false,
        'for_products' => false,
        'for_projects' => false,
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
            'for_pages' => 'boolean',
            'for_posts' => 'boolean',
            'for_products' => 'boolean',
            'for_projects' => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Eloquent Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Get all the pages that are assigned to the category.
     */
    public function pages(): MorphToMany
    {
        return $this->morphedByMany(Page::class, 'categorizable')
            ->orderBy('order_column')
            ->withTimestamps();
    }

    /**
     * Get all the posts that are assigned to the category.
     */
    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'categorizable')
            ->orderBy('order_column')
            ->withTimestamps();
    }

    /**
     * Get all the products that are assigned to the category.
     */
    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'categorizable')
            ->orderBy('order_column')
            ->withTimestamps();
    }

    /**
     * Get all the projects that are assigned to the category.
     */
    public function projects(): MorphToMany
    {
        return $this->morphedByMany(Project::class, 'categorizable')
            ->orderBy('order_column')
            ->withTimestamps();
    }
}
