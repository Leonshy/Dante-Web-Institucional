<section class="section reveal">
    <div class="container">
        <div class="section-head"><h2>Documentos</h2></div>
        @if($documents->isEmpty())
            <x-empty-state icon="file-text">Todavía no hay documentos publicados.</x-empty-state>
        @else
            <div class="cards-grid">
                @foreach($documents as $document)
                    <x-card.section
                        :title="$document->title"
                        :text="collect([$document->category?->name, optional($document->published_at)->format('d/m/Y')])->filter()->implode(' · ')"
                        :url="$document->file?->url()"
                        cta-label="Descargar"
                    />
                @endforeach
            </div>
            <p style="margin-top:var(--spacing-6)"><a class="btn btn-secondary" href="{{ route('documents.index') }}">Ver todos los documentos</a></p>
        @endif
    </div>
</section>
