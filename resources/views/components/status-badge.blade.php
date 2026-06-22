@props(['status'])

<span {{ $attributes->merge(['class' => 'badge text-bg-' . $status->color()]) }}>
    {{ $status->label() }}
</span>
