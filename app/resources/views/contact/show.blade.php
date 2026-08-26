<x-layouts.app title="Contacto — Colegio Dante Alighieri" description="Dirección, teléfono y formulario de contacto del Colegio Dante Alighieri en Asunción.">
    <x-breadcrumbs :items="[['label' => 'Contacto', 'url' => null]]" />
    <main id="contenido" tabindex="-1" class="container section">
        <h1>Contacto</h1>
        <p class="body-lg" style="color:var(--color-neutral-700);margin:var(--spacing-2) 0 var(--spacing-2)">Escríbanos o comuníquese directamente con la secretaría.</p>
        <p style="color:var(--color-neutral-700);margin-bottom:var(--spacing-8)">El colegio atiende consultas académicas y administrativas por separado en cada sede.</p>

        <div class="contact-layout">
            <div class="sede-cards">
                @forelse ($locations as $location)
                    <div class="sede-card">
                        <h2 style="font-size:18px">{{ $location->name }}</h2>
                        <dl>
                            @if($location->academic_email)
                                <dt>Académico</dt>
                                <dd><a href="mailto:{{ $location->academic_email }}">{{ $location->academic_email }}</a></dd>
                            @endif
                            @if($location->administrative_email)
                                <dt>Administrativo</dt>
                                <dd><a href="mailto:{{ $location->administrative_email }}">{{ $location->administrative_email }}</a></dd>
                            @endif
                            @if($location->phone)
                                <dt>Teléfono</dt>
                                <dd>
                                    @foreach (explode('·', $location->phone) as $index => $number)
                                        @php($number = trim($number))
                                        @if($index > 0)
                                            ·
                                        @endif
                                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $number) }}">{{ $number }}</a>
                                    @endforeach
                                </dd>
                            @endif
                            @if($location->whatsapp)
                                <dt>WhatsApp</dt>
                                <dd><a href="https://wa.me/{{ $location->whatsapp }}" target="_blank" rel="noopener">{{ $location->whatsapp }}</a></dd>
                            @endif
                            @if($location->address)
                                <dt>Dirección</dt>
                                <dd>{{ $location->address }}</dd>
                            @endif
                            @if($location->schedule)
                                <dt>Horario de atención</dt>
                                <dd>{{ $location->schedule }}</dd>
                            @endif
                        </dl>

                        @if($location->maps_embed_url)
                            <x-deferred-map :url="$location->maps_embed_url" :title="'Ubicación — sede '.$location->name" />
                        @endif
                    </div>
                @empty
                    <div class="sede-card">
                        <p><span class="pending">[Cargar sedes desde el panel → Sedes]</span></p>
                    </div>
                @endforelse
            </div>

            @include('partials.form-contact')
        </div>
    </main>
</x-layouts.app>
