@props(['icon' => 'inbox'])
<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <p class="icon"><x-dynamic-component :component="'icon.'.$icon" :size="32" /></p>
    <p>{{ $slot }}</p>
    @isset($cta)
        <p style="margin-top:var(--spacing-4)">{{ $cta }}</p>
    @endisset
</div>
