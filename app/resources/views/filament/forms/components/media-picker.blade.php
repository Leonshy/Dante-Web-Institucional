<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <style>
        .dante-media-picker-single { display: flex; align-items: flex-start; gap: 0.75rem; flex-wrap: wrap; }
        .dante-media-picker-trigger { display: block; padding: 0; border: none; background: none; cursor: pointer; }
        .dante-media-picker-img { width: 8rem; height: 8rem; object-fit: cover; border-radius: 0.5rem; display: block; }
        .dante-media-picker-file { display: inline-flex; align-items: center; padding: 0.5rem 0.75rem; border-radius: 0.5rem; background: #71717a; color: #fafafa; font-size: 0.875rem; }
        .dante-media-picker-placeholder { display: flex; align-items: center; justify-content: center; width: 8rem; height: 8rem; border-radius: 0.5rem; border: 2px dashed #71717a; font-size: 0.8rem; text-align: center; padding: 0.5rem; color: #71717a; }
        .dante-media-picker-grid { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem; }
        .dante-media-picker-grid-img { width: 5rem; height: 5rem; object-fit: cover; border-radius: 0.5rem; }
    </style>

    <div class="dante-media-picker">
        @if ($isMultiple())
            @php $items = $selectedMediaItems(); @endphp

            @if ($items->isNotEmpty())
                <div class="dante-media-picker-grid">
                    @foreach ($items as $item)
                        <img
                            src="{{ $item->type === 'image' ? ($item->conversionUrl('small') ?? $item->url()) : '' }}"
                            alt="{{ $item->alt }}"
                            class="dante-media-picker-grid-img"
                        >
                    @endforeach
                </div>
            @endif

            {{ $getAction('choose') }}
        @else
            <div class="dante-media-picker-single">
                {{ $getAction('choose') }}
                @if ($getState())
                    {{ $getAction('remove') }}
                @endif
            </div>
        @endif
    </div>
</x-dynamic-component>
