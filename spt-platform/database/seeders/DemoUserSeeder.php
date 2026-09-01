<?php

// database/seeders/DemoUserSeeder.php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$maria = \App\Models\User::where('email', 'maria.cruz@pnp.gov.ph')->first();

if ($maria) {
    DB::table('notifications')->insert([
        [
            'id' => Str::uuid(),
            'type' => 'App\\Notifications\\Generic',
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $maria->id,
            'data' => json_encode(['message' => 'New course available: Advanced Crisis Management']),
            'read_at' => null,
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ],
        [
            'id' => Str::uuid(),
            'type' => 'App\\Notifications\\Generic',
            'notifiable_type' => \App\Models\User::class,
            'notifiable_id' => $maria->id,
            'data' => json_encode(['message' => 'You have completed Police Ethics and Conduct']),
            'read_at' => null,
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subHours(5),
        ],
    ]);
}

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
                'role' => 'trainee', // was 'officer'
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

            User::firstOrCreate(
                ['email' => 'supervisor.demo@pnp.gov.ph'],
                [
                'name' => 'Supervisor Demo',
                'password' => Hash::make('demo1234'),
                'unit_office' => 'Regional Training Command',
                'two_factor_verified' => true,
                'role' => 'supervisor',
            ]
        );
    }
}
