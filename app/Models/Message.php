<?php

namespace App\Models;

use App\Observers\MessageObserver;
use App\Policies\MessagePolicy;
use App\Traits\LogsTrashedActivity;
use App\Traits\SortableOnUpdate;
use Database\Factories\MessageFactory;
use DateTime;
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
use Spatie\Sluggable\Attributes\Sluggable;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $subject
 * @property string $message
 * @property string|null $source
 * @property bool $read
 * @property bool $replied
 * @property string|null $reply
 * @property DateTime|null $replied_at
 * @property DateTime|null $archived_at
 * @property int $order_column
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property DateTime|null $deleted_at
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property Attribute $archived
 * @property Attribute $unread
 * @property Attribute $title
 * @property BelongsTo $creator
 * @property BelongsTo $updater
 *
 * @method static creator()
 * @method static updater()
 * @method static create(mixed $validated)
 * @method static where(string $string, false $false)
 * @method static forceCreate(array $array)
 */
#[Fillable([
    'slug',
    'name',
    'email',
    'phone',
    'subject',
    'message',
    'source',
    'read',
    'replied',
    'reply',
    'replied_at',
    'archived_at',
    'order_column',
])]
#[Hidden([])]
#[Sluggable(
    from: 'subject',
    to: 'slug',
    separator: '-',
    language: 'nl',
    maxLength: 48,
    unique: true,
    onUpdate: false,
)]
#[ObservedBy(MessageObserver::class)]
#[UsePolicy(MessagePolicy::class)]
class Message extends Model implements Sortable
{
    /** @use HasFactory<MessageFactory> */
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
        'source' => 'contact form',
        'order_column' => 1,
        'read' => false,
        'replied' => false,
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
            'unread' => 'boolean',
            'read' => 'boolean',
            'replied' => 'boolean',
            'replied_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Interact with the archived_at state.
     * Returns true if the archived_at timestamp is set and time is in the past.
     */
    protected function archived(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->archived_at !== null && $this->archived_at <= now(),
        );
    }

    /**
     * Interact with the message unread state.
     * Returns true if 'read' is false.
     */
    protected function unread(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->read === false,
        );
    }

    /**
     * Get title: $this->title
     * Used inside Filament ActivityHistorySection
     */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn () => "$this->subject",
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
        return 'slug';
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
     * Get the user that created the message.
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
     * Get the user that updated the message.
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
