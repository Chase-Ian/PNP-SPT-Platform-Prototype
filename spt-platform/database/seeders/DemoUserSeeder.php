<?php

// database/seeders/DemoUserSeeder.php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'maria.cruz@pnp.gov.ph'],
            [
                'name' => 'Maria Cruz',
                'password' => Hash::make('demo1234'),
                'unit_office' => 'Manila Police District - Station 1 (Ermita)',
                'region' => 'National Capital Region (NCR)',
                'two_factor_verified' => true,
                'role' => 'officer',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin.demo@pnp.gov.ph'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('demo1234'),
                'two_factor_verified' => true,
                'role' => 'admin',
            ]
        );
    }
}
