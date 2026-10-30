<?php

namespace App\Console\Commands;

use App\Services\CartService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('carts:cleanup')]
#[Description('Clear expired carts from the database.')]
class CleanupExpiredCartsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(CartService $cartService): int
    {
        $deletedCount = $cartService->cleanupExpired();

        $this->info("$deletedCount expired cart(s) deleted.");

        return self::SUCCESS;
    }
}
