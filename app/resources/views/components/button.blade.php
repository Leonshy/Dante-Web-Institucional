@props([
    'variant' => 'primary', // primary | secondary | ghost | link | danger
    'href' => null,
    'type' => 'button',
    'loading' => false,
    'disabled' => false,
])
@php
    $class = 'btn btn-'.$variant;
    $tag = $href ? 'a' : 'button';
@endphp
<{{ $tag }}
    {{ $attributes->merge(['class' => $class]) }}
    @if($href) href="{{ $href }}" @else type="{{ $type }}" @endif
    @if($disabled) disabled @endif
    @if($loading) aria-busy="true" @endif
>
    {{ $slot }}
    @if($loading)
        <span class="spinner" aria-hidden="true"></span>
    @endif
</{{ $tag }}>
