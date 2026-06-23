<?php

namespace App\Http\Controllers;

use App\Http\Requests\Issue\IndexIssueRequest;
use App\Http\Requests\Issue\StoreIssueRequest;
use App\Http\Requests\Issue\UpdateIssueRequest;
use App\Models\Issue;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IssueController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Issue::class, 'issue');
    }

    public function index(IndexIssueRequest $request): View|JsonResponse
    {
        $issues = Issue::with(['project', 'tags', 'assignees'])
            ->withCount('comments')
            ->when($request->validated('status'),   fn ($q, $v) => $q->status($v))
            ->when($request->validated('priority'), fn ($q, $v) => $q->priority($v))
            ->when($request->validated('tag'),      fn ($q, $v) => $q->forTag((int) $v))
            ->when($request->validated('q'),        fn ($q, $v) => $q->search($v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'html'       => view('issues.partials.issue-list', [
                    'issues'      => $issues,
                    'showProject' => true,
                ])->render(),
                'pagination' => [
                    'html'      => $issues->hasPages() ? $issues->links()->toHtml() : '',
                    'has_pages' => $issues->hasPages(),
                    'total'     => $issues->total(),
                ],
            ]);
        }

        $filters = [
            'status'   => $request->validated('status', ''),
            'priority' => $request->validated('priority', ''),
            'tag'      => $request->validated('tag', ''),
            'q'        => $request->validated('q', ''),
        ];
        $allTags = Tag::orderBy('name')->get();

        return view('issues.index', compact('issues', 'filters', 'allTags'));
    }

    public function create(): View
    {
        return view('issues.create', [
            'projects'          => Project::orderBy('name')->get(),
            'tags'              => Tag::orderBy('name')->get(),
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
        $issue->loadMissing(['project', 'tags', 'assignees']);
        $allTags  = Tag::orderBy('name')->get();
        $allUsers = User::orderBy('name')->get();

        return view('issues.show', compact('issue', 'allTags', 'allUsers'));
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
