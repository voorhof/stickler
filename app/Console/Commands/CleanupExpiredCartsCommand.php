<?php

namespace App\Console\Commands;

use App\Services\CartService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('carts:cleanup {--force : Force the operation without user confirmation}')]
#[Description('Clear expired carts from the database.')]
class CleanupExpiredCartsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CartService $cartService): int
    {
        if (! $this->option('force')) {
            if ($this->confirm('Are you sure you want to delete all expired carts?')) {
                $deletedCount = $cartService->cleanupExpired();

                $this->info("$deletedCount expired cart(s) deleted.");
            } else {
                $this->info('Cart cleanup cancelled.');
            }
        } else {
            $deletedCount = $cartService->cleanupExpired();

            $this->info("$deletedCount expired cart(s) deleted.");
        }

        return self::SUCCESS;
    }
}
