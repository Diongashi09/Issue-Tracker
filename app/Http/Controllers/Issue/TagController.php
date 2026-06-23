<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\AttachTagRequest;
use App\Models\Issue;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;

class TagController extends Controller
{
    public function store(AttachTagRequest $request, Issue $issue): JsonResponse
    {
        $this->authorize('update', $issue);

        $issue->tags()->syncWithoutDetaching([$request->validated('tag_id')]);

        return $this->tagListResponse($issue);
    }

    public function destroy(Issue $issue, Tag $tag): JsonResponse
    {
        $this->authorize('update', $issue);

        $issue->tags()->detach($tag);

        return $this->tagListResponse($issue);
    }

    private function tagListResponse(Issue $issue): JsonResponse
    {
        $issue->load('tags');
        $allTags = Tag::orderBy('name')->get();

        return response()->json([
            'html' => view('issues.partials.tag-list', compact('issue', 'allTags'))->render(),
        ]);
    }
}
