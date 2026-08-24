@php
    $primaryNav = config('navigation.primary');
    $secondary = config('navigation.footer_secondary');
    $italianEnabled = \App\Models\SiteSetting::italianEnabled();
@endphp
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <img src="{{ asset('images/logo-dante-blanco.svg') }}" alt="Colegio Dante Alighieri" width="234" height="100" class="footer-logo">
                <p class="caption" style="color:var(--color-neutral-400)">Colegio Dante Alighieri — afiliado a la Società Dante Alighieri.</p>
            </div>
            <div>
                <h2>Navegación</h2>
                <ul>
                    @foreach($primaryNav as $item)
                        {{-- Sin página propia (docs/02 §5): el pie no puede abrir un submenú
                             como el header, así que enlaza directo al primer hijo real. --}}
                        <li><a href="{{ url(($item['linkable'] ?? true) ? $item['url'] : ($item['children'][0]['url'] ?? $item['url'])) }}">{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h2>Accesos secundarios</h2>
                <ul>
                    @foreach($secondary as $link)
                        <li><a href="{{ url($link['url']) }}">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h2>Contacto</h2>
                <p class="body-sm">
                    <span class="pending">[COMPLETAR CON DATO REAL DE MIGRACIÓN]</span>
                </p>
                @if($italianEnabled)
                    <div class="lang-toggle" role="group" aria-label="Cambiar idioma del sitio" style="margin-top:var(--spacing-3);border-color:rgba(255,255,255,.3)">
                        <button type="button" style="background:transparent;color:#fff" aria-pressed="{{ app()->getLocale() === 'es' ? 'true' : 'false' }}">ES</button>
                        <button type="button" style="background:transparent;color:#fff" aria-pressed="{{ app()->getLocale() === 'it' ? 'true' : 'false' }}">IT</button>
                    </div>
                @endif
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; {{ now()->year }} Colegio Dante Alighieri. Aviso legal / Privacidad.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook del Colegio Dante Alighieri">f</a>
                <a href="#" aria-label="Instagram del Colegio Dante Alighieri">ig</a>
            </div>
        </div>
    </div>
</footer>
