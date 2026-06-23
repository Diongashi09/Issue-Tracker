<?php

namespace App\Http\Controllers;

use App\Http\Requests\Issue\StoreIssueRequest;
use App\Http\Requests\Issue\UpdateIssueRequest;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IssueController extends Controller
{
    // Phase 5 wires authorizeResource(Issue::class, 'issue') here for edit/update/destroy.

    public function index(): View
    {
        // Eager-load everything the rows render so no N+1 sneaks in before the AJAX
        // layer lands in Phase 4 (blueprint §14). Filters/search are added in Phase 4.
        $issues = Issue::with(['project', 'tags', 'assignees'])
            ->withCount('comments')
            ->latest()
            ->paginate(15);

        return view('issues.index', compact('issues'));
    }

    public function create(): View
    {
        return view('issues.create', [
            'projects'          => Project::orderBy('name')->get(),
            'tags'              => Tag::orderBy('name')->get(),
            // Pre-select the project when arriving from a project's "New Issue" button.
            'selectedProjectId' => request('project'),
        ]);
    }

    public function store(StoreIssueRequest $request): RedirectResponse
    {
        $issue = Issue::create($request->safe()->except('tags'));
        $issue->tags()->sync($request->validated('tags', []));

        return redirect()->route('issues.show', $issue)
            ->with('success', "Issue \"{$issue->title}\" created.");
    }

    public function show(Issue $issue): View
    {
        // project: header breadcrumb; tags: tag-list partial; comments: loaded via AJAX (blueprint §14)
        $issue->loadMissing(['project', 'tags']);
        $allTags = Tag::orderBy('name')->get();

        return view('issues.show', compact('issue', 'allTags'));
    }

    public function edit(Issue $issue): View
    {
        $issue->loadMissing('tags');

        return view('issues.edit', [
            'issue'    => $issue,
            'projects' => Project::orderBy('name')->get(),
            'tags'     => Tag::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateIssueRequest $request, Issue $issue): RedirectResponse
    {
        $issue->update($request->safe()->except('tags'));
        $issue->tags()->sync($request->validated('tags', []));

        return redirect()->route('issues.show', $issue)
            ->with('success', 'Issue updated.');
    }

    public function destroy(Issue $issue): RedirectResponse
    {
        $project = $issue->loadMissing('project')->project;
        $issue->delete();

        return redirect()->route('projects.show', $project)
            ->with('success', 'Issue deleted.');
    }
}
