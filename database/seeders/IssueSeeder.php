<?php

namespace Database\Seeders;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class IssueSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $tags  = Tag::all();

        Project::all()->each(function (Project $project) use ($users, $tags): void {
            $total = rand(8, 12);

            // Build a weighted status pool: ~50% open, ~30% in_progress, ~20% closed.
            // shuffle() then take($total) gives a non-repeating but realistic spread.
            $statuses = collect()
                ->merge(array_fill(0, (int) ceil($total * 0.5), IssueStatus::Open))
                ->merge(array_fill(0, (int) ceil($total * 0.3), IssueStatus::InProgress))
                ->merge(array_fill(0, (int) ceil($total * 0.2), IssueStatus::Closed))
                ->shuffle()
                ->take($total)
                ->values();

            $priorities = collect()
                ->merge(array_fill(0, (int) ceil($total * 0.2), IssuePriority::Low))
                ->merge(array_fill(0, (int) ceil($total * 0.5), IssuePriority::Medium))
                ->merge(array_fill(0, (int) ceil($total * 0.3), IssuePriority::High))
                ->shuffle()
                ->take($total)
                ->values();

            foreach (range(0, $total - 1) as $i) {
                // Mix of upcoming, overdue, and no due dates so filtering is visibly exercised.
                $dueDate = match ($i % 5) {
                    0       => null,
                    1       => fake()->dateTimeBetween('+1 week', '+3 months'),
                    2       => fake()->dateTimeBetween('+3 months', '+1 year'),
                    3       => fake()->dateTimeBetween('-3 weeks', '-1 day'),  // overdue
                    default => null,
                };

                $issue = Issue::factory()->create([
                    'project_id' => $project->id,
                    'status'     => $statuses[$i],
                    'priority'   => $priorities[$i],
                    'due_date'   => $dueDate,
                ]);

                // Attach 1–3 tags from the curated library (unique by collection->random).
                $issue->tags()->attach(
                    $tags->random(rand(1, min(3, $tags->count())))->pluck('id')
                );

                // Assign 0–3 users; rand(0,3) === 0 means unassigned (intentional).
                $assigneeCount = rand(0, 3);
                if ($assigneeCount > 0) {
                    $issue->assignees()->attach(
                        $users->random(min($assigneeCount, $users->count()))->pluck('id')
                    );
                }

                // 0–8 comments so pagination and newest-first ordering are visibly exercised.
                $commentCount = rand(0, 8);
                if ($commentCount > 0) {
                    Comment::factory($commentCount)->for($issue)->create();
                }
            }
        });
    }
}
