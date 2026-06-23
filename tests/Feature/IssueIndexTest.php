<?php

namespace Tests\Feature;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use App\Models\Issue;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueIndexTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Content negotiation
    // -------------------------------------------------------------------------

    public function test_normal_request_returns_full_html_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('issues.index'))
            ->assertOk()
            ->assertViewIs('issues.index')
            ->assertSee('Issues');
    }

    public function test_ajax_request_returns_json(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('issues.index'))
            ->assertOk()
            ->assertJsonStructure(['html', 'pagination' => ['html', 'has_pages', 'total']]);
    }

    public function test_ajax_html_contains_issue_titles(): void
    {
        $user  = User::factory()->create();
        $issue = Issue::factory()->create(['title' => 'Unique Ajax Title']);

        $html = $this->actingAs($user)
            ->getJson(route('issues.index'))
            ->json('html');

        $this->assertStringContainsString('Unique Ajax Title', $html);
    }

    // -------------------------------------------------------------------------
    // Status filter
    // -------------------------------------------------------------------------

    public function test_status_filter_returns_only_matching_issues(): void
    {
        $user   = User::factory()->create();
        $open   = Issue::factory()->create(['status' => IssueStatus::Open,   'title' => 'Open One']);
        $closed = Issue::factory()->create(['status' => IssueStatus::Closed, 'title' => 'Closed One']);

        $html = $this->actingAs($user)
            ->getJson(route('issues.index', ['status' => 'open']))
            ->json('html');

        $this->assertStringContainsString('Open One', $html);
        $this->assertStringNotContainsString('Closed One', $html);
    }

    public function test_invalid_status_returns_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('issues.index', ['status' => 'nonexistent']))
            ->assertUnprocessable();
    }

    // -------------------------------------------------------------------------
    // Priority filter
    // -------------------------------------------------------------------------

    public function test_priority_filter_returns_only_matching_issues(): void
    {
        $user   = User::factory()->create();
        $high   = Issue::factory()->create(['priority' => IssuePriority::High,   'title' => 'High Pri']);
        $low    = Issue::factory()->create(['priority' => IssuePriority::Low,    'title' => 'Low Pri']);

        $html = $this->actingAs($user)
            ->getJson(route('issues.index', ['priority' => 'high']))
            ->json('html');

        $this->assertStringContainsString('High Pri', $html);
        $this->assertStringNotContainsString('Low Pri', $html);
    }

    // -------------------------------------------------------------------------
    // Tag filter
    // -------------------------------------------------------------------------

    public function test_tag_filter_returns_only_issues_with_that_tag(): void
    {
        $user    = User::factory()->create();
        $tag     = Tag::factory()->create(['name' => 'backend']);
        $tagged  = Issue::factory()->create(['title' => 'Tagged Issue']);
        $plain   = Issue::factory()->create(['title' => 'Plain Issue']);
        $tagged->tags()->attach($tag);

        $html = $this->actingAs($user)
            ->getJson(route('issues.index', ['tag' => $tag->id]))
            ->json('html');

        $this->assertStringContainsString('Tagged Issue', $html);
        $this->assertStringNotContainsString('Plain Issue', $html);
    }

    public function test_invalid_tag_id_returns_422(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('issues.index', ['tag' => 99999]))
            ->assertUnprocessable();
    }

    // -------------------------------------------------------------------------
    // Search (q)
    // -------------------------------------------------------------------------

    public function test_search_matches_title(): void
    {
        $user  = User::factory()->create();
        $match = Issue::factory()->create(['title' => 'searchable needle here']);
        $other = Issue::factory()->create(['title' => 'completely unrelated']);

        $html = $this->actingAs($user)
            ->getJson(route('issues.index', ['q' => 'needle']))
            ->json('html');

        $this->assertStringContainsString('searchable needle here', $html);
        $this->assertStringNotContainsString('completely unrelated', $html);
    }

    public function test_search_matches_description(): void
    {
        $user  = User::factory()->create();
        $match = Issue::factory()->create([
            'title'       => 'Normal title',
            'description' => 'contains the search term needle',
        ]);
        $other = Issue::factory()->create(['title' => 'Other issue', 'description' => null]);

        $html = $this->actingAs($user)
            ->getJson(route('issues.index', ['q' => 'needle']))
            ->json('html');

        $this->assertStringContainsString('Normal title', $html);
        $this->assertStringNotContainsString('Other issue', $html);
    }

    // -------------------------------------------------------------------------
    // Filter composition
    // -------------------------------------------------------------------------

    public function test_status_and_priority_filters_compose(): void
    {
        $user = User::factory()->create();

        $match = Issue::factory()->create([
            'title'    => 'Both Match',
            'status'   => IssueStatus::Open,
            'priority' => IssuePriority::High,
        ]);
        $statusOnly = Issue::factory()->create([
            'title'    => 'Status Only',
            'status'   => IssueStatus::Open,
            'priority' => IssuePriority::Low,
        ]);
        $neither = Issue::factory()->create([
            'title'    => 'Neither',
            'status'   => IssueStatus::Closed,
            'priority' => IssuePriority::Low,
        ]);

        $html = $this->actingAs($user)
            ->getJson(route('issues.index', ['status' => 'open', 'priority' => 'high']))
            ->json('html');

        $this->assertStringContainsString('Both Match', $html);
        $this->assertStringNotContainsString('Status Only', $html);
        $this->assertStringNotContainsString('Neither', $html);
    }

    // -------------------------------------------------------------------------
    // Pagination carries filter params (withQueryString)
    // -------------------------------------------------------------------------

    public function test_pagination_total_reflects_filtered_count(): void
    {
        $user = User::factory()->create();

        Issue::factory()->count(3)->create(['status' => IssueStatus::Open]);
        Issue::factory()->count(2)->create(['status' => IssueStatus::Closed]);

        $total = $this->actingAs($user)
            ->getJson(route('issues.index', ['status' => 'open']))
            ->json('pagination.total');

        $this->assertSame(3, $total);
    }

    // -------------------------------------------------------------------------
    // Guest redirect
    // -------------------------------------------------------------------------

    public function test_guest_is_redirected_from_index(): void
    {
        $this->get(route('issues.index'))->assertRedirect(route('login'));
    }
}
