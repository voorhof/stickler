<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessHoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('business_hours')->upsert([
            [
                'key' => 'default',
                'timezone' => null,
                'overflow' => false,
                'hours' => json_encode([
                    'monday' => ['08:00-12:00', '13:00-18:00'],
                    'tuesday' => ['08:00-12:00', '13:00-18:00'],
                    'wednesday' => ['08:00-12:00', '13:00-18:00'],
                    'thursday' => ['08:00-12:00', '13:00-18:00'],
                    'friday' => ['08:00-12:00', '13:00-18:00'],
                    'saturday' => [],
                    'sunday' => [],
                ]),
                'exceptions' => json_encode([]),
            ],
        ], ['key']);
    }
}
