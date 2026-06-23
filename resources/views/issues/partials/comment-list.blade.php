@forelse ($comments as $comment)
    @include('issues.partials.comment-item', compact('comment'))
@empty
    <div class="comment-empty list-group-item text-muted small fst-italic py-3">No comments yet.</div>
@endforelse
