<div class="list-group-item py-3">
    <div class="d-flex gap-3">
        <div class="flex-shrink-0">
            {{-- Initial avatar --}}
            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                 style="width:34px;height:34px;font-size:.8rem;" aria-hidden="true">
                {{ mb_strtoupper(mb_substr($comment->author_name, 0, 1)) }}
            </div>
        </div>
        <div class="flex-grow-1" style="min-width:0;">
            <div class="d-flex align-items-baseline gap-2 mb-1">
                <span class="fw-semibold small">{{ $comment->author_name }}</span>
                <time class="text-muted" style="font-size:.75rem;"
                      datetime="{{ $comment->created_at->toIso8601String() }}"
                      title="{{ $comment->created_at->format('M j, Y g:i A') }}">
                    {{ $comment->created_at->diffForHumans() }}
                </time>
            </div>
            <div class="small" style="white-space:pre-wrap;">{{ $comment->body }}</div>
        </div>
    </div>
</div>
