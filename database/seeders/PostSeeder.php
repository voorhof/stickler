<?php

namespace Database\Seeders;

use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Post::factory()->count(15)->create();
        Post::factory()->count(5)->notPublished()->create();
        Post::factory()->count(3)->softDeleted()->create();
        Post::factory()->count(3)->notPublished()->softDeleted()->create();
    }
}
