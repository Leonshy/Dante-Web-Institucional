@props(['data'])
<div class="section">
    <div class="container">
        <div class="cards-grid">
            @foreach($data['items'] ?? [] as $item)
                <x-card.section :title="$item['title'] ?? ''" :text="$item['text'] ?? null" :url="$item['url'] ?? null" />
            @endforeach
        </div>
    </div>
</div>
