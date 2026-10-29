<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserAndRoleSeeder::class,
            BusinessHoursSeeder::class,
            MessageSeeder::class,
            PageSeeder::class,
            PostSeeder::class,
            ProductSeeder::class,
            ProjectSeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
