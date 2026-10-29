<?php

/** @noinspection PhpUndefinedFieldInspection */

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariantOption;
use App\Models\ProductVariantOptionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colorType = ProductVariantOptionType::create(['name' => 'Kleur']);
        $colorOptions = collect([
            ['name' => 'Blauw', 'value' => '#3B82F6'],
            ['name' => 'Rood', 'value' => '#EF4444'],
            ['name' => 'Groen', 'value' => '#22C55E'],
        ])->map(fn (array $data) => ProductVariantOption::create([
            ...$data,
            'product_variant_option_type_id' => $colorType->id,
        ]));

        $sizeType = ProductVariantOptionType::create(['name' => 'Maat']);
        $sizeOptions = collect([
            ['name' => 'SM', 'value' => null],
            ['name' => 'M', 'value' => null],
            ['name' => 'LG', 'value' => null],
            ['name' => 'XL', 'value' => null],
        ])->map(fn (array $data) => ProductVariantOption::create([
            ...$data,
            'product_variant_option_type_id' => $sizeType->id,
        ]));

        $products = Product::factory()->count(15)->hasVariants(3)->create();
        $products->each(fn (Product $product) => $this->attachOptionsToVariants($product, $colorOptions, $sizeOptions));

        $products = Product::factory()->count(5)->hasVariants(2)->notPublished()->create();
        $products->each(fn (Product $product) => $this->attachOptionsToVariants($product, $colorOptions, $sizeOptions));

        $products = Product::factory()->count(3)->hasVariants(2)->softDeleted()->create();
        $products->each(fn (Product $product) => $this->attachOptionsToVariants($product, $colorOptions, $sizeOptions));

        $products = Product::factory()->count(3)->hasVariants(1)->notPublished()->softDeleted()->create();
        $products->each(fn (Product $product) => $this->attachOptionsToVariants($product, $colorOptions, $sizeOptions));
    }

    /**
     * Attach one color and one size option to each variant of the product.
     */
    protected function attachOptionsToVariants(Product $product, Collection $colorOptions, Collection $sizeOptions): void
    {
        foreach ($product->variants as $variant) {
            $color = $colorOptions->random();
            $size = $sizeOptions->random();
            $variant->options()->attach([$color->id, $size->id]);
        }
    }
}
