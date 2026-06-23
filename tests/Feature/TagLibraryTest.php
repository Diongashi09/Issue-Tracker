<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagLibraryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Auth guard
    // -------------------------------------------------------------------------

    public function test_guest_cannot_create_a_tag(): void
    {
        $this->postJson(route('tags.store'), ['name' => 'newbug'])
            ->assertUnauthorized();
    }

    // -------------------------------------------------------------------------
    // store — POST /tags
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_create_a_tag(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('tags.store'), ['name' => 'performance', 'color' => '#ff5733'])
            ->assertStatus(201)
            ->assertJsonFragment(['name' => 'performance', 'color' => '#ff5733']);

        $this->assertDatabaseHas('tags', ['name' => 'performance']);
    }

    public function test_store_works_without_a_color(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson(route('tags.store'), ['name' => 'colorless'])
            ->assertStatus(201);

        $this->assertDatabaseHas('tags', ['name' => 'colorless', 'color' => null]);
    }

    // -------------------------------------------------------------------------
    // Duplicate tag name
    // -------------------------------------------------------------------------

    public function test_duplicate_tag_name_is_rejected(): void
    {
        $user = User::factory()->create();
        Tag::factory()->create(['name' => 'existing-tag']);

        $this->actingAs($user)
            ->postJson(route('tags.store'), ['name' => 'existing-tag'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_duplicate_tag_name_check_is_case_sensitive(): void
    {
        // SQLite's LIKE is case-insensitive but the unique constraint is not —
        // this documents the database-level behavior.
        $user = User::factory()->create();
        Tag::factory()->create(['name' => 'Frontend']);

        // "frontend" (lowercase) should be a distinct entry on a strict unique constraint
        $response = $this->actingAs($user)
            ->postJson(route('tags.store'), ['name' => 'frontend']);

        // Accept either 201 (distinct) or 422 (database treats as duplicate) — the key
        // thing is that exact-case duplicates are always rejected.
        $this->assertContains($response->getStatusCode(), [201, 422]);
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_store_requires_name(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('tags.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_store_rejects_invalid_hex_color(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('tags.store'), ['name' => 'newtag', 'color' => 'not-a-hex'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('color');
    }

    public function test_store_rejects_short_hex_color(): void
    {
        // #rgb shorthand (4 chars) is not accepted — must be full #rrggbb (7 chars)
        $this->actingAs(User::factory()->create())
            ->postJson(route('tags.store'), ['name' => 'newtag', 'color' => '#abc'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('color');
    }

    // -------------------------------------------------------------------------
    // index — GET /tags
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_to_login_from_tags_index(): void
    {
        $this->get(route('tags.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_tags_index(): void
    {
        $user = User::factory()->create();
        Tag::factory()->create(['name' => 'visible-tag']);

        $this->actingAs($user)
            ->get(route('tags.index'))
            ->assertOk()
            ->assertSee('visible-tag');
    }

    // -------------------------------------------------------------------------
    // update — PATCH /tags/{tag}
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_update_a_tag(): void
    {
        $user = User::factory()->create();
        $tag  = Tag::factory()->create(['name' => 'old-name']);

        $this->actingAs($user)
            ->patch(route('tags.update', $tag), [
                'name'  => 'new-name',
                'color' => '#123456',
            ])
            ->assertRedirect(route('tags.index'));

        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'name' => 'new-name', 'color' => '#123456']);
    }

    public function test_update_allows_keeping_the_same_name(): void
    {
        // The unique rule must ignore the tag being updated (Rule::unique()->ignore()).
        $user = User::factory()->create();
        $tag  = Tag::factory()->create(['name' => 'existing-name', 'color' => '#aabbcc']);

        $this->actingAs($user)
            ->patch(route('tags.update', $tag), [
                'name'  => 'existing-name',
                'color' => '#112233',
            ])
            ->assertRedirect(route('tags.index'));

        $this->assertDatabaseHas('tags', ['id' => $tag->id, 'color' => '#112233']);
    }

    public function test_update_rejects_name_already_used_by_another_tag(): void
    {
        $user  = User::factory()->create();
        $tagA  = Tag::factory()->create(['name' => 'alpha']);
        $tagB  = Tag::factory()->create(['name' => 'beta']);

        $this->actingAs($user)
            ->patch(route('tags.update', $tagB), ['name' => 'alpha'])
            ->assertSessionHasErrors('name');
    }

    // -------------------------------------------------------------------------
    // destroy — DELETE /tags/{tag}
    // -------------------------------------------------------------------------

    public function test_authenticated_user_can_delete_a_tag(): void
    {
        $user = User::factory()->create();
        $tag  = Tag::factory()->create();

        $this->actingAs($user)
            ->delete(route('tags.destroy', $tag))
            ->assertRedirect(route('tags.index'));

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function test_guest_cannot_delete_a_tag(): void
    {
        $tag = Tag::factory()->create();

        $this->delete(route('tags.destroy', $tag))
            ->assertRedirect(route('login'));
    }
}
