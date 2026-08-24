@props(['data'])
<div class="section" style="background:var(--color-neutral-100)">
    <div class="container">
        <div class="stats-grid">
            @foreach($data['items'] ?? [] as $item)
                <div>
                    <span class="stat-number">{{ $item['number'] ?? '' }}</span>
                    <p>{{ $item['label'] ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
