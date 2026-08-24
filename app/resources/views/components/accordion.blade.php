@props(['items']) {{-- array de ['question' => string, 'answer' => string (HTML)] --}}
<div {{ $attributes->merge(['class' => 'accordion']) }}>
    @foreach($items as $index => $item)
        <div class="accordion-item" x-data="{ open: {{ $index === 0 ? 'true' : 'false' }} }">
            <button type="button" class="accordion-trigger" :aria-expanded="open.toString()"
                    aria-controls="accordion-panel-{{ $attributes->get('id', 'a') }}-{{ $index }}"
                    @click="open = !open">
                <span>{{ $item['question'] }}</span>
                <span class="chev" aria-hidden="true"><x-icon.chevron-down /></span>
            </button>
            <div class="accordion-panel" :class="{ 'is-open': open }" :inert="!open"
                 id="accordion-panel-{{ $attributes->get('id', 'a') }}-{{ $index }}">
                <div class="accordion-panel-inner">{!! $item['answer'] !!}</div>
            </div>
        </div>
    @endforeach
</div>
