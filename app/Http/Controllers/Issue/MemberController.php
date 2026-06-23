<?php

namespace App\Http\Controllers\Issue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Issue\AttachMemberRequest;
use App\Models\Issue;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class MemberController extends Controller
{
    public function store(AttachMemberRequest $request, Issue $issue): JsonResponse
    {
        $this->authorize('update', $issue);

        $issue->assignees()->syncWithoutDetaching([$request->validated('user_id')]);

        return $this->memberListResponse($issue);
    }

    public function destroy(Issue $issue, User $user): JsonResponse
    {
        $this->authorize('update', $issue);

        $issue->assignees()->detach($user);

        return $this->memberListResponse($issue);
    }

    private function memberListResponse(Issue $issue): JsonResponse
    {
        $issue->load('assignees');
        $allUsers = User::orderBy('name')->get();

        return response()->json([
            'html' => view('issues.partials.member-list', compact('issue', 'allUsers'))->render(),
        ]);
    }
}
