<?php

namespace Database\Factories;

use App\Models\Issue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'issue_id'    => Issue::factory(),
            'author_name' => fake()->name(),
            'body'        => fake()->paragraphs(rand(1, 3), true),
        ];
    }
}
