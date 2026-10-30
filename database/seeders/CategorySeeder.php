<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
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

        $pages = Page::all();
        $pageCategories = Category::all()->where('for_pages', true);
        foreach ($pages as $page) {
            $page->categories()->sync($pageCategories->random(3)->pluck('id'));
        }

        $posts = Post::all();
        $postCategories = Category::all()->where('for_posts', true);
        foreach ($posts as $post) {
            $post->categories()->sync($postCategories->random(3)->pluck('id'));
        }

        $products = Product::all();
        $productCategories = Category::all()->where('for_products', true);
        foreach ($products as $product) {
            $product->categories()->sync($productCategories->random(3)->pluck('id'));
        }

        $projects = Project::all();
        $projectCategories = Category::all()->where('for_projects', true);
        foreach ($projects as $project) {
            $project->categories()->sync($projectCategories->random(3)->pluck('id'));
        }
    }
}
