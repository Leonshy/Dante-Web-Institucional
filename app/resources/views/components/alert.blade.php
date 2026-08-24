@props(['variant' => 'info', 'role' => null])
@php
    $role = $role ?? ($variant === 'danger' ? 'alert' : 'status');
@endphp
<div {{ $attributes->merge(['class' => 'alert alert-'.$variant]) }} role="{{ $role }}">
    @switch($variant)
        @case('success')
            <x-icon.check-circle />
            @break
        @case('danger')
            <x-icon.alert-circle />
            @break
        @default
            <x-icon.alert-circle />
    @endswitch
    <div>{{ $slot }}</div>
</div>
