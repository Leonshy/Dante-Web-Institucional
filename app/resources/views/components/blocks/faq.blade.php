@props(['data'])
<div class="section">
    <div class="container" style="max-width:760px">
        <x-accordion :items="collect($data['items'] ?? [])->map(fn ($i) => ['question' => $i['question'] ?? '', 'answer' => $i['answer'] ?? ''])->all()" />
    </div>
</div>
