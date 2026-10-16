<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Page::factory()->count(15)->create();
        Page::factory()->count(5)->notPublished()->create();
        Page::factory()->count(3)->softDeleted()->create();
        Page::factory()->count(3)->notPublished()->softDeleted()->create();
    }
}
