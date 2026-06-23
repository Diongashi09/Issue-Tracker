<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tag\StoreTagRequest;
use App\Http\Requests\Tag\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        $tags = Tag::withCount('issues')->orderBy('name')->get();

        return view('tags.index', compact('tags'));
    }

    public function store(StoreTagRequest $request): JsonResponse|RedirectResponse
    {
        $tag = Tag::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json($tag, 201);
        }

        return redirect()->route('tags.index')
            ->with('success', "Tag \"{$tag->name}\" created.");
    }

    public function edit(Tag $tag): View
    {
        return view('tags.edit', compact('tag'));
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        return redirect()->route('tags.index')
            ->with('success', "Tag \"{$tag->name}\" updated.");
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $name = $tag->name;
        $tag->delete(); // issue_tag cascade handles pivot cleanup

        return redirect()->route('tags.index')
            ->with('success', "Tag \"{$name}\" deleted.");
    }
}
