<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    // Helper: create an issue owned by $user (project.user_id === $user->id).
    private function issueOwnedBy(User $user): Issue
    {
        $project = Project::factory()->create(['user_id' => $user->id]);

        return Issue::factory()->create(['project_id' => $project->id]);
    }

    // -------------------------------------------------------------------------
    // Auth guards
    // -------------------------------------------------------------------------

    public function test_guest_cannot_assign_a_member(): void
    {
        $issue = Issue::factory()->create();
        $user  = User::factory()->create();

        $this->postJson(route('issues.members.store', $issue), ['user_id' => $user->id])
            ->assertUnauthorized();
    }

    public function test_guest_cannot_unassign_a_member(): void
    {
        $issue = Issue::factory()->create();
        $user  = User::factory()->create();
        $issue->assignees()->attach($user);

        $this->deleteJson(route('issues.members.destroy', [$issue, $user]))
            ->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // store — POST issues/{issue}/members
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_assign_a_member(): void
    {
        $actor = User::factory()->create();
        $issue = $this->issueOwnedBy($actor);
        $user  = User::factory()->create();

        $this->actingAs($actor)
            ->postJson(route('issues.members.store', $issue), ['user_id' => $user->id])
            ->assertOk()
            ->assertJsonStructure(['html']);

        $this->assertDatabaseHas('issue_user', [
            'issue_id' => $issue->id,
            'user_id'  => $user->id,
        ]);
    }

    public function test_assign_returns_html_containing_the_new_member(): void
    {
        $actor = User::factory()->create();
        $issue = $this->issueOwnedBy($actor);
        $user  = User::factory()->create(['name' => 'Alice Tester']);

        $html = $this->actingAs($actor)
            ->postJson(route('issues.members.store', $issue), ['user_id' => $user->id])
            ->json('html');

        $this->assertStringContainsString('Alice Tester', $html);
    }

    public function test_assign_is_idempotent_no_duplicate_pivot_row(): void
    {
        $actor = User::factory()->create();
        $issue = $this->issueOwnedBy($actor);
        $user  = User::factory()->create();

        $this->actingAs($actor)
            ->postJson(route('issues.members.store', $issue), ['user_id' => $user->id])
            ->assertOk();

        $this->actingAs($actor)
            ->postJson(route('issues.members.store', $issue), ['user_id' => $user->id])
            ->assertOk();

        $this->assertSame(
            1,
            \DB::table('issue_user')
                ->where('issue_id', $issue->id)
                ->where('user_id', $user->id)
                ->count(),
            'Duplicate pivot row was inserted',
        );
    }

    public function test_assign_html_omits_already_assigned_user_from_select(): void
    {
        $actor = User::factory()->create();
        $issue = $this->issueOwnedBy($actor);
        $user  = User::factory()->create(['name' => 'Bob Assignee']);

        $html = $this->actingAs($actor)
            ->postJson(route('issues.members.store', $issue), ['user_id' => $user->id])
            ->json('html');

        $this->assertStringNotContainsString(
            '<option value="' . $user->id . '">Bob Assignee</option>',
            $html,
        );
    }

    // -------------------------------------------------------------------------
    // destroy — DELETE issues/{issue}/members/{user}
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_unassign_a_member(): void
    {
        $actor = User::factory()->create();
        $issue = $this->issueOwnedBy($actor);
        $user  = User::factory()->create();
        $issue->assignees()->attach($user);

        $this->actingAs($actor)
            ->deleteJson(route('issues.members.destroy', [$issue, $user]))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $this->assertDatabaseMissing('issue_user', [
            'issue_id' => $issue->id,
            'user_id'  => $user->id,
        ]);
    }

    public function test_unassign_returns_html_no_longer_containing_removed_member(): void
    {
        $actor = User::factory()->create();
        $issue = $this->issueOwnedBy($actor);
        $user  = User::factory()->create(['name' => 'Carol Removed']);
        $issue->assignees()->attach($user);

        $html = $this->actingAs($actor)
            ->deleteJson(route('issues.members.destroy', [$issue, $user]))
            ->json('html');

        $this->assertStringContainsString('No members assigned', $html);
    }

    public function test_unassign_user_not_on_issue_still_returns_ok(): void
    {
        $actor = User::factory()->create();
        $issue = $this->issueOwnedBy($actor);
        $user  = User::factory()->create();

        $this->actingAs($actor)
            ->deleteJson(route('issues.members.destroy', [$issue, $user]))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_store_requires_user_id(): void
    {
        $actor = User::factory()->create();
        $issue = $this->issueOwnedBy($actor);

        $this->actingAs($actor)
            ->postJson(route('issues.members.store', $issue), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('user_id');
    }

    public function test_store_user_id_must_exist(): void
    {
        $actor = User::factory()->create();
        $issue = $this->issueOwnedBy($actor);

        $this->actingAs($actor)
            ->postJson(route('issues.members.store', $issue), ['user_id' => 99999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('user_id');
    }
}
