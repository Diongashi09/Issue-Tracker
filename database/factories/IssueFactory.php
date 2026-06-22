<?php

namespace Database\Factories;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Issue>
 */
class IssueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id'  => Project::factory(),
            'title'       => ucfirst(fake()->sentence(rand(4, 9), false)),
            'description' => fake()->boolean(70) ? fake()->paragraphs(rand(1, 3), true) : null,
            'status'      => fake()->randomElement(IssueStatus::cases()),
            'priority'    => fake()->randomElement(IssuePriority::cases()),
            'due_date'    => fake()->boolean(50) ? fake()->dateTimeBetween('-1 month', '+3 months') : null,
        ];
    }

    public function open(): static
    {
        return $this->state(['status' => IssueStatus::Open]);
    }

    public function inProgress(): static
    {
        return $this->state(['status' => IssueStatus::InProgress]);
    }

    public function closed(): static
    {
        return $this->state(['status' => IssueStatus::Closed]);
    }

    public function highPriority(): static
    {
        return $this->state(['priority' => IssuePriority::High]);
    }
}
