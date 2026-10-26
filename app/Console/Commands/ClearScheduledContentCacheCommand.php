<?php

/** @noinspection PhpUndefinedMethodInspection */

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\ResponseCache\Facades\ResponseCache;

#[Signature('content:clear-scheduled-cache')]
#[Description('Clear the response cache when scheduled content becomes public.')]
class ClearScheduledContentCacheCommand extends Command
{
    private const string LAST_CHECKED_CACHE_KEY = 'scheduled-content:last-checked-at';

    /** @var list<class-string<Model>> */
    private const array PUBLISHABLE_MODELS = [Post::class, Page::class, Project::class];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();
        $lastCheckedAt = Cache::get(self::LAST_CHECKED_CACHE_KEY);

        if (! config('responsecache.enabled')) {
            Cache::forever(self::LAST_CHECKED_CACHE_KEY, $now->toIso8601String());

            return self::SUCCESS;
        }

        $hasNewlyPublishedContent = $lastCheckedAt === null;

        if ($lastCheckedAt !== null) {
            $lastCheckedAt = Carbon::parse($lastCheckedAt);
            $hasNewlyPublishedContent = collect(self::PUBLISHABLE_MODELS)
                ->contains(fn (string $modelClass): bool => $modelClass::becamePublishedBetween($lastCheckedAt, $now)->exists());
        }

        if ($hasNewlyPublishedContent) {
            ResponseCache::clear();
            $this->info('Response cache cleared for newly published content.');
        }

        Cache::forever(self::LAST_CHECKED_CACHE_KEY, $now->toIso8601String());

        return self::SUCCESS;
    }
}
