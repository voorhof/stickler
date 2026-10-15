<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\ResponsiveImages\Jobs\GenerateResponsiveImagesJob;

class GenerateMissingResponsiveImagesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:generate-missing-responsive-images {--queue : Queue the generation jobs instead of running synchronously}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate responsive images for existing media records missing responsive_images data.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = Media::query()
            ->where('mime_type', 'LIKE', 'image/%')
            ->where(function ($query) {
                $query->whereNull('responsive_images')
                    ->orWhere('responsive_images', '[]')
                    ->orWhere('responsive_images', '{}');
            });

        $count = $query->count();

        if ($count === 0) {
            $this->info('No media records with missing responsive images found.');

            return self::SUCCESS;
        }

        $this->info("Found $count media items to process.");
        $progressBar = $this->output->createProgressBar($count);

        $shouldQueue = (bool) $this->option('queue');

        $jobClass = config('media-library.jobs.generate_responsive_images', GenerateResponsiveImagesJob::class);
        $queueConnection = config('media-library.queue_connection_name');
        $queueName = config('media-library.queue_name');
        $afterCommit = (bool) config('media-library.queue_conversions_after_database_commit');

        $query->chunkById(100, function ($mediaItems) use ($progressBar, $shouldQueue, $jobClass, $queueConnection, $queueName, $afterCommit): void {
            foreach ($mediaItems as $media) {
                $job = new $jobClass($media);

                if ($shouldQueue) {
                    if ($queueConnection) {
                        $job->onConnection($queueConnection);
                    }

                    if ($queueName) {
                        $job->onQueue($queueName);
                    }

                    $afterCommit ? dispatch($job)->afterCommit() : dispatch($job);
                } else {
                    dispatch_sync($job);
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);
        $this->info('Done!');

        return self::SUCCESS;
    }
}
