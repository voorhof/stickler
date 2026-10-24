<?php

namespace App\Console\Commands;

use App\Models\PageView;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

#[Signature('page-views:clean {--days=60 : Keep page views from the last number of days} {--force : Run without confirmation}')]
#[Description('Delete page views older than the given number of days.')]
class CleanPageViewsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $maxAgeInDays = (int) $this->option('days');

        if ($maxAgeInDays < 0 || $maxAgeInDays > 366) {
            $this->error('The --days option must be between 0 and 366.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm("Delete all page views older than {$maxAgeInDays} days?")) {
            return self::SUCCESS;
        }

        $deletedCount = PageView::where('viewed_on', '<', Carbon::today()->subDays($maxAgeInDays)->toDateString())->delete();

        $this->info("{$deletedCount} page views deleted.");

        return self::SUCCESS;
    }
}
