<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Guest redirects — every project route must require authentication
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_to_login_from_index(): void
    {
        $this->get(route('projects.index'))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_from_create(): void
    {
        $this->get(route('projects.create'))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_from_show(): void
    {
        $project = Project::factory()->create();
        $this->get(route('projects.show', $project))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_from_edit(): void
    {
        $project = Project::factory()->create();
        $this->get(route('projects.edit', $project))->assertRedirect(route('login'));
    }

    public function test_guest_cannot_store_a_project(): void
    {
        $this->post(route('projects.store'), ['name' => 'X'])->assertRedirect(route('login'));
    }

    public function test_guest_cannot_update_a_project(): void
    {
        $project = Project::factory()->create();
        $this->patch(route('projects.update', $project), ['name' => 'X'])->assertRedirect(route('login'));
    }

    public function test_guest_cannot_delete_a_project(): void
    {
        $project = Project::factory()->create();
        $this->delete(route('projects.destroy', $project))->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_authenticated_user_sees_projects_list(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->for($user, 'owner')->create(['name' => 'Atlas Initiative']);

        $this->actingAs($user)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Atlas Initiative');
    }

    public function test_index_shows_projects_owned_by_multiple_users(): void
    {
        $alice = User::factory()->create();
        $bob   = User::factory()->create();

        Project::factory()->for($alice, 'owner')->create(['name' => 'Alice Project']);
        Project::factory()->for($bob, 'owner')->create(['name' => 'Bob Project']);

        // Alice sees all projects on the index (viewAny is open to any auth user)
        $this->actingAs($alice)
            ->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Alice Project')
            ->assertSee('Bob Project');
    }

    // -------------------------------------------------------------------------
    // Create / Store
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_view_the_create_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('projects.create'))
            ->assertOk();
    }

    public function test_authenticated_user_can_create_a_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('projects.store'), [
            'name'        => 'Orion Platform',
            'description' => 'A new project.',
            'start_date'  => '2026-01-01',
            'deadline'    => '2026-12-31',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('projects', [
            'name'    => 'Orion Platform',
            'user_id' => $user->id,
        ]);
    }

    public function test_store_sets_owner_to_authenticated_user_regardless_of_payload(): void
    {
        $owner  = User::factory()->create();
        $other  = User::factory()->create();

        // Even if a forged user_id is in the request body, the owner must be $owner.
        $this->actingAs($owner)->post(route('projects.store'), [
            'name'    => 'Ownership Check',
            'user_id' => $other->id, // forged — must be ignored
        ]);

        $this->assertDatabaseHas('projects', [
            'name'    => 'Ownership Check',
            'user_id' => $owner->id,
        ]);
        $this->assertDatabaseMissing('projects', [
            'name'    => 'Ownership Check',
            'user_id' => $other->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_owner_can_view_their_project(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->for($user, 'owner')->create(['name' => 'Visible Project']);

        $this->actingAs($user)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Visible Project');
    }

    public function test_any_authenticated_user_can_view_any_project(): void
    {
        $owner  = User::factory()->create();
        $viewer = User::factory()->create();
        $project = Project::factory()->for($owner, 'owner')->create(['name' => 'Shared View']);

        // view() returns true for any auth user — not ownership-restricted
        $this->actingAs($viewer)
            ->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Shared View');
    }

    // -------------------------------------------------------------------------
    // Edit / Update — owner
    // -------------------------------------------------------------------------

    public function test_owner_can_view_the_edit_form(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->for($user, 'owner')->create();

        $this->actingAs($user)
            ->get(route('projects.edit', $project))
            ->assertOk();
    }

    public function test_owner_can_update_their_project(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->for($user, 'owner')->create();

        $this->actingAs($user)
            ->patch(route('projects.update', $project), [
                'name'        => 'Renamed Project',
                'description' => 'Updated description.',
            ])
            ->assertRedirect(route('projects.show', $project));

        $this->assertDatabaseHas('projects', [
            'id'   => $project->id,
            'name' => 'Renamed Project',
        ]);
    }

    // -------------------------------------------------------------------------
    // Destroy — owner
    // -------------------------------------------------------------------------

    public function test_owner_can_delete_their_project(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->for($user, 'owner')->create();

        $this->actingAs($user)
            ->delete(route('projects.destroy', $project))
            ->assertRedirect(route('projects.index'));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    // -------------------------------------------------------------------------
    // Authorization — non-owner gets 403
    // -------------------------------------------------------------------------

    public function test_non_owner_cannot_view_the_edit_form(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->for($owner, 'owner')->create();

        $this->actingAs($other)
            ->get(route('projects.edit', $project))
            ->assertForbidden();
    }

    public function test_non_owner_cannot_update_a_project(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->for($owner, 'owner')->create();

        $this->actingAs($other)
            ->patch(route('projects.update', $project), ['name' => 'Hijacked'])
            ->assertForbidden();

        // DB unchanged
        $this->assertDatabaseMissing('projects', ['id' => $project->id, 'name' => 'Hijacked']);
    }

    public function test_non_owner_cannot_delete_a_project(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $project = Project::factory()->for($owner, 'owner')->create();

        $this->actingAs($other)
            ->delete(route('projects.destroy', $project))
            ->assertForbidden();

        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_store_requires_a_name(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('projects.store'), [])
            ->assertSessionHasErrors('name');
    }

    public function test_update_requires_a_name(): void
    {
        $user    = User::factory()->create();
        $project = Project::factory()->for($user, 'owner')->create();

        $this->actingAs($user)
            ->patch(route('projects.update', $project), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_deadline_must_be_on_or_after_start_date(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('projects.store'), [
                'name'       => 'Bad Dates',
                'start_date' => '2026-12-01',
                'deadline'   => '2026-01-01', // before start_date
            ])
            ->assertSessionHasErrors('deadline');
    }

    public function test_deadline_is_valid_when_no_start_date_is_given(): void
    {
        $user = User::factory()->create();

        // deadline without a start_date should be accepted
        $this->actingAs($user)
            ->post(route('projects.store'), [
                'name'     => 'No Start Date',
                'deadline' => '2026-06-01',
            ])
            ->assertSessionDoesntHaveErrors('deadline');

        $this->assertDatabaseHas('projects', ['name' => 'No Start Date']);
    }
}
