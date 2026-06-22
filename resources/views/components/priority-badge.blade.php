@props(['priority'])

<span {{ $attributes->merge(['class' => 'badge text-bg-' . $priority->color()]) }}>
    {{ $priority->label() }}
</span>
