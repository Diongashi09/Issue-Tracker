{{--
  Used for initial server render (issues.show) and AJAX refresh (Issue\TagController).
  Receives: $issue (tags eager-loaded), $allTags (full Tag library ordered by name).
  JS swaps #tags-section's innerHTML with this partial on every attach/detach.
--}}
@php
    $attachedIds = $issue->tags->pluck('id');
    $available   = $allTags->whereNotIn('id', $attachedIds->all());
@endphp

<div class="d-flex flex-wrap gap-1">
    @forelse ($issue->tags as $tag)
        <x-tag-badge :tag="$tag" :removable="true"
                     :destroyUrl="route('issues.tags.destroy', [$issue, $tag])" />
    @empty
        <span class="text-muted small fst-italic">No tags attached.</span>
    @endforelse
</div>

@if ($available->isNotEmpty())
    <form id="tag-attach-form" class="d-flex gap-2 mt-3" novalidate
          data-store-url="{{ route('issues.tags.store', $issue) }}">
        <select class="form-select form-select-sm" name="tag_id" id="tag-select">
            <option value="">Add a tag…</option>
            @foreach ($available as $tag)
                <option value="{{ $tag->id }}">{{ $tag->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-outline-secondary btn-sm text-nowrap">
            Add
        </button>
    </form>
@endif
