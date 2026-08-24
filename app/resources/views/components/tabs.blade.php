@props(['tabs']) {{-- array de ['label' => string, 'content' => slot/html] --}}
<div x-data="{ active: 0 }" {{ $attributes }}>
    <div class="tabs-list" role="tablist">
        @foreach($tabs as $index => $tab)
            <button type="button" role="tab" :aria-selected="(active === {{ $index }}).toString()"
                    @click="active = {{ $index }}">
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>
    @foreach($tabs as $index => $tab)
        <div class="tab-panel" :class="{ 'is-active': active === {{ $index }} }" x-show="active === {{ $index }}" role="tabpanel">
            {!! $tab['content'] !!}
        </div>
    @endforeach
</div>
