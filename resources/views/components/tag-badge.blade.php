@props(['tag', 'removable' => false])

<span {{ $attributes->merge(['class' => 'badge rounded-pill']) }}
      style="background-color: {{ $tag->color ?? '#6c757d' }}; color: #fff;">
    {{ $tag->name }}
    @if ($removable)
        {{-- detach button wired in Phase 4 (AJAX tag controller) --}}
        <button type="button"
                class="btn-close btn-close-white ms-1"
                style="font-size: .5rem;"
                data-issue-id="{{ $tag->pivot->issue_id ?? '' }}"
                data-tag-id="{{ $tag->id }}"
                aria-label="Remove tag">
        </button>
    @endif
</span>
