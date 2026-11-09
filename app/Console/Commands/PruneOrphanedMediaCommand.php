<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('media:prune-orphans {--force : Force the operation without user confirmation} {--dry-run : List orphaned media without deleting}')]
#[Description('Prune media files and records that are no longer associated with any active model.')]
class PruneOrphanedMediaCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('Are you sure you want to prune orphaned media files and records?')) {
                $this->info('Media cleanup cancelled.');

                return self::SUCCESS;
            }
        }

        $this->call('media-library:clean', [
            '--delete-orphaned' => true,
            '--dry-run' => $this->option('dry-run'),
        ]);

        return self::SUCCESS;
    }
}
