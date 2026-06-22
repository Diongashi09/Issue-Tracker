<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Fixed demo user — credentials documented in README so the reviewer can log straight in.
        User::factory()->create([
            'name'  => 'Demo User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        // Four more users who will own projects.
        User::factory(4)->create();

        // Two users with zero owned projects — demonstrates the authorization boundary:
        // they can browse but cannot edit anyone else's projects or issues.
        User::factory(2)->create();
    }
}
