<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Issue;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // index — GET issues/{issue}/comments
    // -------------------------------------------------------------------------

    public function test_guest_cannot_load_comments(): void
    {
        $issue = Issue::factory()->create();

        $this->getJson(route('issues.comments.index', $issue))
            ->assertUnauthorized();
    }

    public function test_index_returns_json_with_html_and_pagination(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create();
        Comment::factory()->count(3)->for($issue)->create();

        $response = $this->actingAs($user)
            ->getJson(route('issues.comments.index', $issue));

        $response->assertOk()
            ->assertJsonStructure([
                'html',
                'pagination' => ['current_page', 'last_page', 'has_more_pages'],
            ]);
    }

    public function test_index_returns_newest_first(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create();

        $old = Comment::factory()->for($issue)->create(['author_name' => 'Alice', 'created_at' => now()->subHour()]);
        $new = Comment::factory()->for($issue)->create(['author_name' => 'Bob',   'created_at' => now()]);

        $html = $this->actingAs($user)
            ->getJson(route('issues.comments.index', $issue))
            ->json('html');

        // Newest (Bob) should appear before oldest (Alice) in the rendered HTML
        $this->assertLessThan(strpos($html, 'Alice'), strpos($html, 'Bob'));
    }

    public function test_index_paginates_at_ten(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create();
        Comment::factory()->count(12)->for($issue)->create();

        $response = $this->actingAs($user)
            ->getJson(route('issues.comments.index', $issue));

        $response->assertOk()
            ->assertJsonPath('pagination.has_more_pages', true)
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.last_page', 2);
    }

    public function test_index_page_two_returns_remaining_comments(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create();
        Comment::factory()->count(12)->for($issue)->create();

        $response = $this->actingAs($user)
            ->getJson(route('issues.comments.index', $issue) . '?page=2');

        $response->assertOk()
            ->assertJsonPath('pagination.current_page', 2)
            ->assertJsonPath('pagination.has_more_pages', false);
    }

    // -------------------------------------------------------------------------
    // store — POST issues/{issue}/comments
    // -------------------------------------------------------------------------

    public function test_guest_cannot_post_a_comment(): void
    {
        $issue = Issue::factory()->create();

        $this->postJson(route('issues.comments.store', $issue), [
            'author_name' => 'Alice',
            'body'        => 'Hello!',
        ])->assertUnauthorized();
    }

    public function test_authenticated_user_can_post_a_comment(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('issues.comments.store', $issue), [
                'author_name' => 'Alice',
                'body'        => 'Great work on this issue.',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['html']);

        $this->assertDatabaseHas('comments', [
            'issue_id'    => $issue->id,
            'author_name' => 'Alice',
            'body'        => 'Great work on this issue.',
        ]);
    }

    public function test_store_returns_rendered_comment_html(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create();

        $html = $this->actingAs($user)
            ->postJson(route('issues.comments.store', $issue), [
                'author_name' => 'Carol',
                'body'        => 'Rendered comment check.',
            ])
            ->json('html');

        $this->assertStringContainsString('Carol', $html);
        $this->assertStringContainsString('Rendered comment check.', $html);
    }

    // -------------------------------------------------------------------------
    // Validation — store
    // -------------------------------------------------------------------------

    public function test_store_requires_author_name(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create();

        $this->actingAs($user)
            ->postJson(route('issues.comments.store', $issue), ['body' => 'No name.'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('author_name');
    }

    public function test_store_requires_body(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create();

        $this->actingAs($user)
            ->postJson(route('issues.comments.store', $issue), ['author_name' => 'Alice'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');
    }

    public function test_store_author_name_max_255(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create();

        $this->actingAs($user)
            ->postJson(route('issues.comments.store', $issue), [
                'author_name' => str_repeat('a', 256),
                'body'        => 'Hello.',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('author_name');
    }

    public function test_store_body_max_5000(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create();

        $this->actingAs($user)
            ->postJson(route('issues.comments.store', $issue), [
                'author_name' => 'Alice',
                'body'        => str_repeat('x', 5001),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');
    }

    public function test_store_422_response_contains_errors_bag(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create();

        $this->actingAs($user)
            ->postJson(route('issues.comments.store', $issue), [])
            ->assertUnprocessable()
            ->assertJsonStructure(['message', 'errors']);
    }
}
