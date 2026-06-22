<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Use the first 5 users as project owners (the last 2 intentionally own nothing).
        $owners = User::orderBy('id')->take(5)->get();

        // 10 projects spread across the 5 owners (2 each on average).
        // recycle() reuses existing users rather than creating new ones.
        Project::factory(10)->recycle($owners)->create();
    }
}
