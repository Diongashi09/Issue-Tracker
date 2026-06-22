<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,    // users first — projects FK to users
            TagSeeder::class,     // tags are independent
            ProjectSeeder::class, // projects FK to users
            IssueSeeder::class,   // issues + pivots + comments all depend on the above
        ]);
    }
}
