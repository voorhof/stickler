<?php

namespace App\Models;

use Database\Factories\PageViewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $path
 * @property string|null $route_name
 * @property string|null $referrer_host
 * @property string $visitor_hash
 * @property bool $is_bot
 * @property string $viewed_on
 * @property string $created_at
 *
 * @method static create(array $array)
 * @method static count()
 * @method static where(string $string, string $today)
 */
#[Fillable([
    'path',
    'route_name',
    'referrer_host',
    'visitor_hash',
    'is_bot',
    'viewed_on',
])]
class PageView extends Model
{
    /** @use HasFactory<PageViewFactory> */
    use HasFactory;

    public const null UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'is_bot' => 'boolean',
        ];
    }
}
