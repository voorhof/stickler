<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()->count(15)->create();
        Product::factory()->count(5)->notPublished()->create();
        Product::factory()->count(3)->softDeleted()->create();
        Product::factory()->count(3)->notPublished()->softDeleted()->create();
    }
}
