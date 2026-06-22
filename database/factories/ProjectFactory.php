<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => ucwords(fake()->words(rand(2, 4), true)) . ' Project',
            'description' => fake()->paragraph(),
            'start_date'  => fake()->dateTimeBetween('-6 months', '-1 week'),
            'deadline'    => fake()->dateTimeBetween('+2 weeks', '+6 months'),
        ];
    }
}
