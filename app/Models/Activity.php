<?php

namespace App\Models;

use App\Policies\ActivityPolicy;
use App\Traits\HasStandardActivitylogOptions;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Enums\ActivityEvent;
use Spatie\Activitylog\Models\Activity as SpatieActivity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;

/**
 * @property Attribute $title
 *
 * @method static distinct()
 * @method static where(string $string, string $string1)
 * @method static create(string[] $array)
 * @method static truncate()
 * @method static inLog(string|array $string)
 * @method static causedBy(User $user)
 * @method static forSubject(Model $model)
 * @method static forEvent(string|ActivityEvent $string)
 * @method static factory()
 */
#[UsePolicy(ActivityPolicy::class)]
class Activity extends SpatieActivity
{
    use HasStandardActivitylogOptions {
        HasStandardActivitylogOptions::getActivitylogOptions insteadof LogsActivity;
    }
    use LogsActivity;

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
            get: fn () => "$this->description",
        )->shouldCache();
    }

    /*
    |--------------------------------------------------------------------------
    | Spatie Activitylog
    |--------------------------------------------------------------------------
    */

    // Log only deleted activities. This is the only available action on Activity Model.
    protected static array $recordEvents = ['deleted'];

    /**
     * Determine whether to log only fillable attributes.
     */
    protected function activitylogFillable(): bool
    {
        return false;
    }

    /**
     * Get the attributes that should be ignored when determining if changes occurred.
     *
     * @return array<string>
     */
    protected function activitylogIgnoreChanges(): array
    {
        return ['updated_at', 'updated_by'];
    }

    /**
     * Get the custom log name.
     */
    protected function activitylogName(): ?string
    {
        return 'activity';
    }

    /**
     * Admin Filament route to the activity subject or causer model
     */
    public function activityModelRoute($modelType): string
    {
        $modelRouteSlug = str_replace('app\\models\\', '', mb_strtolower($modelType)).'s';

        if ($modelRouteSlug === 'medias') {
            return 'filament.admin.resources.media.edit';
        }

        return 'filament.admin.resources.'.$modelRouteSlug.'.edit';
    }
}
