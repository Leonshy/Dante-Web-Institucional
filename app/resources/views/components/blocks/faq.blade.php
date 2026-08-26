@props(['data'])
@php
    $items = collect($data['items'] ?? [])->map(fn ($i) => ['question' => $i['question'] ?? '', 'answer' => $i['answer'] ?? ''])->all();
    // JSON-LD FAQPage (docs/08-seo.md §3) — solo si hay preguntas con
    // respuesta real, evita advertencias del validador con bloques vacíos.
    $faqSchema = collect($items)
        ->filter(fn ($item) => trim((string) $item['question']) !== '' && trim((string) strip_tags($item['answer'])) !== '')
        ->map(fn ($item) => [
            '@type' => 'Question',
            'name' => strip_tags($item['question']),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => strip_tags($item['answer']),
            ],
        ])
        ->values();
@endphp
@if($faqSchema->isNotEmpty())
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqSchema->all(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endif
<div class="section">
    <div class="container" style="max-width:760px">
        <x-accordion :items="$items" />
    </div>
</div>
