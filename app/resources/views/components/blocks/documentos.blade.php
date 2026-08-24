@if($documents->isEmpty())
    <x-empty-state icon="file-text">No hay documentos publicados en esta categoría todavía.</x-empty-state>
@else
    <ul class="doc-link-list">
        @foreach($documents as $document)
            <li>
                <span>{{ $document->title }}</span>
                <a href="{{ $document->file?->url() }}" aria-label="Descargar {{ $document->title }} en PDF" download>
                    <x-icon.download size="14" /> Descargar
                </a>
            </li>
        @endforeach
    </ul>
@endif
