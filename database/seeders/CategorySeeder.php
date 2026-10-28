<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::factory()->count(15)->create();
        Category::factory()->count(3)->softDeleted()->create();

        $products = Product::all();
        $productCategories = Category::all()->where('for_products', 1);

        foreach ($products as $product) {
            $product->categories()->sync($productCategories->random(3)->pluck('id'));
        }
    }
}
