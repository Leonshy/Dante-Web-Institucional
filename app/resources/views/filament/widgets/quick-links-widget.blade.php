<x-filament-widgets::widget>
    <x-filament::section heading="Accesos directos">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem">
            @foreach ($this->getLinks() as $link)
                <x-filament::link :href="$link['url']" :icon="$link['icon']">
                    {{ $link['label'] }}
                </x-filament::link>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
