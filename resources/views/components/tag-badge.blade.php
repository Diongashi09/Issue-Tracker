@props(['tag', 'removable' => false, 'destroyUrl' => null])

<span {{ $attributes->merge(['class' => 'badge rounded-pill']) }}
      style="background-color: {{ $tag->color ?? '#6c757d' }}; color: #fff;">
    {{ $tag->name }}
    @if ($removable)
        <button type="button"
                class="btn-close btn-close-white ms-1"
                style="font-size:.5rem;"
                @if ($destroyUrl) data-destroy-url="{{ $destroyUrl }}" @endif
                aria-label="Remove {{ $tag->name }}">
        </button>
    @endif
</span>
