<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tag::factory()->count(10)->create();
        Tag::factory()->count(3)->softDeleted()->create();

        $projects = Project::all();

        foreach ($projects as $project) {
            $project->tags()->attach(Tag::inRandomOrder()->take(2)->pluck('id'));
        }

        $posts = Post::all();

        foreach ($posts as $post) {
            $post->tags()->attach(Tag::inRandomOrder()->take(2)->pluck('id'));
        }
    }
}
