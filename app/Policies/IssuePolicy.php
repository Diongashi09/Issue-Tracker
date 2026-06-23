<?php

namespace App\Policies;

use App\Models\Issue;
use App\Models\User;

class IssuePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Issue $issue): bool
    {
        return true; // any authenticated user may read an issue
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Issue $issue): bool
    {
        // loadMissing avoids an N+1 when the policy fires before the controller
        // has had a chance to eager-load the relationship (e.g. on edit/destroy).
        return $user->id === $issue->loadMissing('project')->project->user_id;
    }

    public function delete(User $user, Issue $issue): bool
    {
        return $user->id === $issue->loadMissing('project')->project->user_id;
    }
}
