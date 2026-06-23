<?php

namespace Tests\Feature;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function issueOwnedBy(User $user): Issue
    {
        $project = Project::factory()->create(['user_id' => $user->id]);

        return Issue::factory()->create(['project_id' => $project->id]);
    }

    private function validPayload(int $projectId, array $overrides = []): array
    {
        return array_merge([
            'project_id' => $projectId,
            'title'      => 'Sample Issue Title',
            'status'     => IssueStatus::Open->value,
            'priority'   => IssuePriority::Medium->value,
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // Guest redirects — every issue route must require authentication
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_to_login_from_index(): void
    {
        $this->get(route('issues.index'))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_from_create(): void
    {
        $this->get(route('issues.create'))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store_an_issue(): void
    {
        $this->post(route('issues.store'), ['title' => 'X'])->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_from_show(): void
    {
        $issue = Issue::factory()->create();

        $this->get(route('issues.show', $issue))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_from_edit(): void
    {
        $issue = Issue::factory()->create();

        $this->get(route('issues.edit', $issue))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_an_issue(): void
    {
        $issue = Issue::factory()->create();

        $this->patch(route('issues.update', $issue), ['title' => 'X'])->assertRedirect(route('login'));
    }

    public function test_guest_cannot_delete_an_issue(): void
    {
        $issue = Issue::factory()->create();

        $this->delete(route('issues.destroy', $issue))->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_any_authenticated_user_can_view_an_issue(): void
    {
        $viewer = User::factory()->create();
        $issue  = Issue::factory()->create(['title' => 'Viewable Issue']);

        $this->actingAs($viewer)
            ->get(route('issues.show', $issue))
            ->assertOk()
            ->assertSee('Viewable Issue');
    }

    // -------------------------------------------------------------------------
    // Create / Store
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_view_the_create_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('issues.create'))
            ->assertOk();
    }

    public function test_authenticated_user_can_create_an_issue(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->post(route('issues.store'), $this->validPayload($project->id, [
                'title' => 'Brand New Issue',
            ]));

        $response->assertRedirect();
        $this->assertDatabaseHas('issues', [
            'project_id' => $project->id,
            'title'      => 'Brand New Issue',
        ]);
    }

    public function test_store_syncs_tags(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);
        $tagA    = Tag::factory()->create();
        $tagB    = Tag::factory()->create();

        $this->actingAs($user)
            ->post(route('issues.store'), $this->validPayload($project->id, [
                'tags' => [$tagA->id, $tagB->id],
            ]));

        $issue = Issue::where('project_id', $project->id)->firstOrFail();

        $this->assertDatabaseHas('issue_tag', ['issue_id' => $issue->id, 'tag_id' => $tagA->id]);
        $this->assertDatabaseHas('issue_tag', ['issue_id' => $issue->id, 'tag_id' => $tagB->id]);
    }

    // -------------------------------------------------------------------------
    // Edit / Update — owner only
    // -------------------------------------------------------------------------

    public function test_owner_can_view_the_edit_form(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);

        $this->actingAs($user)
            ->get(route('issues.edit', $issue))
            ->assertOk();
    }

    public function test_owner_can_update_their_issue(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);

        $this->actingAs($user)
            ->patch(route('issues.update', $issue), $this->validPayload($issue->project_id, [
                'title'  => 'Updated Title',
                'status' => IssueStatus::Closed->value,
            ]))
            ->assertRedirect(route('issues.show', $issue));

        $this->assertDatabaseHas('issues', [
            'id'     => $issue->id,
            'title'  => 'Updated Title',
            'status' => IssueStatus::Closed->value,
        ]);
    }

    // -------------------------------------------------------------------------
    // Destroy — owner only
    // -------------------------------------------------------------------------

    public function test_owner_can_delete_their_issue(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);

        $this->actingAs($user)
            ->delete(route('issues.destroy', $issue))
            ->assertRedirect();

        $this->assertDatabaseMissing('issues', ['id' => $issue->id]);
    }

    // -------------------------------------------------------------------------
    // Authorization — non-owner gets 403
    // -------------------------------------------------------------------------

    public function test_non_owner_cannot_view_the_edit_form(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $issue = $this->issueOwnedBy($owner);

        $this->actingAs($other)
            ->get(route('issues.edit', $issue))
            ->assertForbidden();
    }

    public function test_non_owner_cannot_update_an_issue(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $issue = $this->issueOwnedBy($owner);

        $this->actingAs($other)
            ->patch(route('issues.update', $issue), $this->validPayload($issue->project_id, [
                'title' => 'Hijacked',
            ]))
            ->assertForbidden();

        $this->assertDatabaseMissing('issues', ['id' => $issue->id, 'title' => 'Hijacked']);
    }

    public function test_non_owner_cannot_delete_an_issue(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $issue = $this->issueOwnedBy($owner);

        $this->actingAs($other)
            ->delete(route('issues.destroy', $issue))
            ->assertForbidden();

        $this->assertDatabaseHas('issues', ['id' => $issue->id]);
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_store_requires_title(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('issues.store'), $this->validPayload($project->id, ['title' => '']))
            ->assertSessionHasErrors('title');
    }

    public function test_store_requires_project_id(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('issues.store'), [
                'title'    => 'No Project',
                'status'   => IssueStatus::Open->value,
                'priority' => IssuePriority::Medium->value,
            ])
            ->assertSessionHasErrors('project_id');
    }

    public function test_store_rejects_invalid_status(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('issues.store'), $this->validPayload($project->id, [
                'status' => 'not_a_status',
            ]))
            ->assertSessionHasErrors('status');
    }

    public function test_store_rejects_invalid_priority(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->post(route('issues.store'), $this->validPayload($project->id, [
                'priority' => 'not_a_priority',
            ]))
            ->assertSessionHasErrors('priority');
    }

    public function test_store_rejects_nonexistent_project(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('issues.store'), $this->validPayload(99999))
            ->assertSessionHasErrors('project_id');
    }
}
