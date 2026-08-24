<x-layouts.app title="Contacto — Colegio Dante Alighieri" description="Dirección, teléfono y formulario de contacto del Colegio Dante Alighieri en Asunción.">
    <x-breadcrumbs :items="[['label' => 'Contacto', 'url' => null]]" />
    <main id="contenido" class="container section">
        <h1>Contacto</h1>
        <p class="body-lg" style="color:var(--color-neutral-700);margin:var(--spacing-2) 0 var(--spacing-8)">Escríbanos o comuníquese directamente con la secretaría.</p>

        <div class="contact-layout">
            <div class="contact-info">
                <h2 style="font-size:20px">Datos de contacto</h2>
                <dl>
                    <dt>Dirección</dt><dd><span class="pending">[COMPLETAR CON DATO REAL DE MIGRACIÓN]</span></dd>
                    <dt>Teléfono</dt><dd><span class="pending">[COMPLETAR CON DATO REAL DE MIGRACIÓN]</span></dd>
                    <dt>Email</dt><dd><span class="pending">[COMPLETAR CON DATO REAL DE MIGRACIÓN]</span></dd>
                    <dt>Horario de atención</dt><dd><span class="pending">[COMPLETAR CON DATO REAL DE MIGRACIÓN]</span></dd>
                </dl>
                <div class="map-embed" role="img" aria-label="Ubicación del Colegio Dante Alighieri en el mapa — pendiente de dirección real">[Mapa embebido]</div>
            </div>

            @include('partials.form-contact')
        </div>
    </main>
</x-layouts.app>
