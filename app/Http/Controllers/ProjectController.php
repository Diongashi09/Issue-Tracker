<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        $projects = Project::with('owner')
            ->withCount('issues')
            ->latest()
            ->paginate(15);

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        // Use the relationship to create so user_id is set by the ORM,
        // never through mass assignment (user_id is excluded from $fillable).
        $project = auth()->user()->projects()->create($request->validated());

        return redirect()->route('projects.show', $project)
            ->with('success', "Project \"{$project->name}\" created.");
    }

    public function show(Project $project): View
    {
        // Load owner for the detail header; not needed at list level.
        $project->loadMissing('owner');

        $issues = $project->issues()
            ->with(['tags', 'assignees'])
            ->withCount('comments')
            ->latest()
            ->paginate(15);

        return view('projects.show', compact('project', 'issues'));
    }

    public function edit(Project $project): View
    {
        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted.');
    }
}
