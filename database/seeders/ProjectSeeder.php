<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::factory()->count(15)->create();
        Project::factory()->count(5)->notPublished()->create();
        Project::factory()->count(3)->softDeleted()->create();
        Project::factory()->count(3)->notPublished()->softDeleted()->create();
    }
}
