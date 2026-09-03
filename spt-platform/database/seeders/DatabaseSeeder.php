<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CourseSeeder::class,
            DemoUserSeeder::class,
        ]);
        // Test User factory call removed — clean database, only the 3 demo accounts
    }
}