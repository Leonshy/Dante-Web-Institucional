@props(['data'])
<x-hero
    :title="$data['title'] ?? ''"
    :subtitle="$data['subtitle'] ?? null"
    :image="!empty($data['image']) ? \Illuminate\Support\Facades\Storage::disk('public')->url($data['image']) : null"
    :cta-label="$data['cta_label'] ?? null"
    :cta-url="$data['cta_url'] ?? null"
/>
