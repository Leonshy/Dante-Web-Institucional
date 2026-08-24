<div>
    <div class="searchbar" style="margin-top:var(--spacing-6)">
        <label for="search-q">Buscar en el sitio</label>
        <input id="search-q" type="search" wire:model.live.debounce.400ms="query" autofocus>
        <button type="button" aria-label="Buscar en el sitio"><x-icon.search /></button>
    </div>

    <div class="filter-tabs" role="group" aria-label="Filtrar por tipo de contenido">
        <button type="button" wire:click="setType('todo')" aria-pressed="{{ $type === 'todo' ? 'true' : 'false' }}">Todo</button>
        <button type="button" wire:click="setType('page')" aria-pressed="{{ $type === 'page' ? 'true' : 'false' }}">Páginas</button>
        <button type="button" wire:click="setType('post')" aria-pressed="{{ $type === 'post' ? 'true' : 'false' }}">Noticias</button>
        <button type="button" wire:click="setType('document')" aria-pressed="{{ $type === 'document' ? 'true' : 'false' }}">Documentos</button>
        <button type="button" wire:click="setType('announcement')" aria-pressed="{{ $type === 'announcement' ? 'true' : 'false' }}">Comunicados</button>
    </div>

    <div wire:loading.delay class="demo-block" aria-hidden="true">
        <div class="skeleton w-60"></div>
        <div class="skeleton w-40"></div>
        <div class="skeleton w-90"></div>
    </div>

    <div wire:loading.remove.delay>
        @if(mb_strlen(trim($query)) < 2)
            <p class="result-count">Escriba al menos 2 caracteres para buscar.</p>
        @elseif($this->results->isEmpty())
            <x-empty-state icon="search">
                No encontramos resultados para «{{ $query }}». Pruebe con otra palabra.
            </x-empty-state>
        @else
            <p class="result-count">{{ $this->results->count() }} {{ $this->results->count() === 1 ? 'resultado' : 'resultados' }} para «{{ $query }}»</p>
            <div>
                @foreach($this->results as $result)
                    <div class="result">
                        <span class="chip">{{ $result->typeLabel }}</span><br>
                        @if($result->url)
                            <a href="{{ $result->url }}">{{ $result->title }}</a>
                        @else
                            <span style="font-weight:700;font-size:18px">{{ $result->title }}</span>
                        @endif
                        @if($result->excerpt)
                            <p>{{ $result->excerpt }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
