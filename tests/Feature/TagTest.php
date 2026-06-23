<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
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

    public function test_guest_cannot_attach_a_tag(): void
    {
        $issue = Issue::factory()->create();
        $tag   = Tag::factory()->create();

        $this->postJson(route('issues.tags.store', $issue), ['tag_id' => $tag->id])
            ->assertUnauthorized();
    }

    public function test_guest_cannot_detach_a_tag(): void
    {
        $issue = Issue::factory()->create();
        $tag   = Tag::factory()->create();
        $issue->tags()->attach($tag);

        $this->deleteJson(route('issues.tags.destroy', [$issue, $tag]))
            ->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // store — POST issues/{issue}/tags
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_attach_a_tag(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);
        $tag   = Tag::factory()->create();

        $this->actingAs($user)
            ->postJson(route('issues.tags.store', $issue), ['tag_id' => $tag->id])
            ->assertOk()
            ->assertJsonStructure(['html']);

        $this->assertDatabaseHas('issue_tag', [
            'issue_id' => $issue->id,
            'tag_id'   => $tag->id,
        ]);
    }

    public function test_attach_returns_html_containing_the_new_tag(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);
        $tag   = Tag::factory()->create(['name' => 'urgent']);

        $html = $this->actingAs($user)
            ->postJson(route('issues.tags.store', $issue), ['tag_id' => $tag->id])
            ->json('html');

        $this->assertStringContainsString('urgent', $html);
    }

    public function test_attach_is_idempotent_no_duplicate_pivot_row(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);
        $tag   = Tag::factory()->create();

        $this->actingAs($user)
            ->postJson(route('issues.tags.store', $issue), ['tag_id' => $tag->id])
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('issues.tags.store', $issue), ['tag_id' => $tag->id])
            ->assertOk();

        $this->assertSame(
            1,
            \DB::table('issue_tag')
                ->where('issue_id', $issue->id)
                ->where('tag_id', $tag->id)
                ->count(),
            'Duplicate pivot row was inserted',
        );
    }

    public function test_attach_html_omits_already_attached_tag_from_select(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);
        $tag   = Tag::factory()->create(['name' => 'frontend']);

        $html = $this->actingAs($user)
            ->postJson(route('issues.tags.store', $issue), ['tag_id' => $tag->id])
            ->json('html');

        $this->assertStringNotContainsString(
            '<option value="' . $tag->id . '">frontend</option>',
            $html,
        );
    }

    // -------------------------------------------------------------------------
    // destroy — DELETE issues/{issue}/tags/{tag}
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_detach_a_tag(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);
        $tag   = Tag::factory()->create();
        $issue->tags()->attach($tag);

        $this->actingAs($user)
            ->deleteJson(route('issues.tags.destroy', [$issue, $tag]))
            ->assertOk()
            ->assertJsonStructure(['html']);

        $this->assertDatabaseMissing('issue_tag', [
            'issue_id' => $issue->id,
            'tag_id'   => $tag->id,
        ]);
    }

    public function test_detach_returns_html_no_longer_containing_removed_tag(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);
        $tag   = Tag::factory()->create(['name' => 'wontfix']);
        $issue->tags()->attach($tag);

        $html = $this->actingAs($user)
            ->deleteJson(route('issues.tags.destroy', [$issue, $tag]))
            ->json('html');

        $this->assertStringContainsString('No tags attached', $html);
    }

    public function test_detach_tag_not_on_issue_still_returns_ok(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);
        $tag   = Tag::factory()->create();

        $this->actingAs($user)
            ->deleteJson(route('issues.tags.destroy', [$issue, $tag]))
            ->assertOk();
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_store_requires_tag_id(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);

        $this->actingAs($user)
            ->postJson(route('issues.tags.store', $issue), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tag_id');
    }

    public function test_store_tag_id_must_exist(): void
    {
        $user  = User::factory()->create();
        $issue = $this->issueOwnedBy($user);

        $this->actingAs($user)
            ->postJson(route('issues.tags.store', $issue), ['tag_id' => 99999])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tag_id');
    }
}
