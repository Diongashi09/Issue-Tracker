<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Models\Issue;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    /**
     * Return a page of comments for an issue, newest-first.
     *
     * Response: { html: rendered comment-list, pagination: { current_page, last_page, has_more_pages } }
     */
    public function index(Issue $issue): JsonResponse
    {
        $comments = $issue->comments()->latest()->paginate(10);

        return response()->json([
            'html'       => view('issues.partials.comment-list', compact('comments'))->render(),
            'pagination' => [
                'current_page'  => $comments->currentPage(),
                'last_page'     => $comments->lastPage(),
                'has_more_pages' => $comments->hasMorePages(),
            ],
        ]);
    }

    /**
     * Store a new comment and return the rendered comment item for prepending.
     *
     * Response (201): { html: rendered comment-item }
     */
    public function store(StoreCommentRequest $request, Issue $issue): JsonResponse
    {
        $comment = $issue->comments()->create($request->validated());

        return response()->json([
            'html' => view('issues.partials.comment-item', compact('comment'))->render(),
        ], 201);
    }
}
