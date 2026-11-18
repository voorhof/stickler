<?php

namespace App\Models;

use App\Observers\PermissionObserver;
use App\Policies\PermissionPolicy;
use App\Traits\HasCreatorAndUpdater;
use App\Traits\HasStandardActivitylogOptions;
use App\Traits\LogsTrashedActivity;
use Database\Factories\PermissionFactory;
use DateTime;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property DateTime $created_at
 * @property DateTime $updated_at
 * @property int|null $created_by_user_id
 * @property int|null $updated_by_user_id
 * @property Attribute $title
 * @property BelongsTo $creator
 * @property BelongsTo $updater
 *
 * @method static creator()
 * @method static updater()
 * @method static where(string $string, mixed $guard_name)
 */
#[Fillable([
    'name',
    'guard_name',
])]
#[ObservedBy(PermissionObserver::class)]
#[UsePolicy(PermissionPolicy::class)]
class Permission extends SpatiePermission
{
    use HasCreatorAndUpdater;

    /** @use HasFactory<PermissionFactory> */
    use HasFactory;

    use HasStandardActivitylogOptions {
        HasStandardActivitylogOptions::getActivitylogOptions insteadof LogsActivity;
    }
    use LogsActivity;
    use LogsTrashedActivity;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'guard_name' => 'web',
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
}
