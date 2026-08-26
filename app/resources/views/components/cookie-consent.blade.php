{{--
    Banner de consentimiento de cookies (docs/08-seo.md §6). Bloquea de verdad
    los scripts de terceros: GTM/GA4 y Meta Pixel no se cargan con ningún
    <script src> incondicional en el layout — los inyecta `resources/js/consent.js`
    recién cuando hay consentimiento guardado, y solo para las categorías
    aceptadas. "Rechazar todo" es un botón igual de visible que "Aceptar todo"
    (misma jerarquía visual, un solo clic cada uno).
--}}
<div
    x-data="danteCookieConsent()"
    x-show="visible"
    x-cloak
    x-transition
    class="cookie-banner"
    role="dialog"
    aria-modal="false"
    aria-label="Preferencias de cookies"
>
    <template x-if="!configuring">
        <div>
            <p>
                Usamos cookies propias y de terceros para medir el uso del sitio y mostrar
                contenido de redes sociales. Podés aceptar todas, rechazar todas o elegir cuáles.
                <a href="{{ url('/institucion/politica-de-cookies') }}" style="text-decoration:underline">Más información</a>.
            </p>
            <div class="actions">
                <x-button variant="secondary" type="button" x-on:click="rejectAll()">Rechazar todo</x-button>
                <x-button variant="secondary" type="button" x-on:click="configuring = true">Configurar</x-button>
                <x-button variant="primary" type="button" x-on:click="acceptAll()">Aceptar todo</x-button>
            </div>
        </div>
    </template>

    <template x-if="configuring">
        <div>
            <p>Elegí qué categorías de cookies permitís. Las necesarias no se pueden desactivar.</p>
            <div class="actions" style="flex-direction:column;align-items:flex-start;gap:var(--spacing-2)">
                <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" checked disabled> Necesarias (siempre activas)</label>
                <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" x-model="analytics"> Analítica (Google Analytics)</label>
                <label style="display:flex;gap:8px;align-items:center"><input type="checkbox" x-model="marketing"> Marketing (Meta Pixel)</label>
            </div>
            <div class="actions">
                <x-button variant="secondary" type="button" x-on:click="rejectAll()">Rechazar todo</x-button>
                <x-button variant="primary" type="button" x-on:click="saveConfigured()">Guardar preferencias</x-button>
            </div>
        </div>
    </template>
</div>

<button
    type="button"
    x-data="{ open() { window.dispatchEvent(new CustomEvent('dante:reopen-consent')) } }"
    x-on:click="open()"
    class="btn-link"
    style="position:fixed;left:var(--spacing-4);bottom:var(--spacing-4);z-index:299;background:#fff;border-radius:var(--radius-md);padding:var(--spacing-2) var(--spacing-3);box-shadow:var(--shadow-md)"
    aria-label="Cambiar preferencias de cookies"
>
    Cookies
</button>
