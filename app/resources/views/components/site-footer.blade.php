@php
    $primaryNav = \App\Models\Menu::renderTree('primary');
    $secondary = \App\Models\Menu::renderTree('footer_secondary');
    $italianEnabled = \App\Models\SiteSetting::italianEnabled();
    $facebookUrl = \App\Models\SiteSetting::get('social_facebook_url');
    $instagramUrl = \App\Models\SiteSetting::get('social_instagram_url');
    $locations = \App\Models\Location::query()->active()->orderBy('sort_order')->get();
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
                <p class="body-sm"><a href="{{ url('/contacto') }}" style="color:inherit;text-decoration:underline">Ver los contactos por sede →</a></p>
                @if($italianEnabled)
                    <div class="lang-toggle" role="group" aria-label="Cambiar idioma del sitio" style="margin-top:var(--spacing-3)">
                        <a href="{{ route('locale.switch', 'es') }}" aria-current="{{ app()->getLocale() === 'es' ? 'true' : 'false' }}">ES</a>
                        <a href="{{ route('locale.switch', 'it') }}" aria-current="{{ app()->getLocale() === 'it' ? 'true' : 'false' }}">IT</a>
                    </div>
                @endif
            </div>
        </div>

        @if($locations->isNotEmpty())
            {{-- Cada sede administrada desde el panel ("Sedes") en su propia
                 columna — antes acá solo había una sede escrita a mano
                 (Asunción), sin importar cuántas sedes tuviera el colegio. --}}
            <div class="footer-sedes">
                <h2>Nuestras sedes</h2>
                <div class="footer-sedes-grid">
                    @foreach ($locations as $location)
                        <div class="footer-sede">
                            <h3>{{ $location->name }}</h3>
                            @if($location->address)
                                <p class="body-sm">{{ $location->address }}</p>
                            @endif
                            @if($location->phone)
                                <p class="body-sm">
                                    @foreach (explode('·', $location->phone) as $index => $number)
                                        @php($number = trim($number))
                                        @if($index > 0)
                                            ·
                                        @endif
                                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $number) }}" style="color:inherit">{{ $number }}</a>
                                    @endforeach
                                </p>
                            @endif
                            @if($location->academic_email)
                                <p class="body-sm"><a href="mailto:{{ $location->academic_email }}" style="color:inherit">{{ $location->academic_email }}</a></p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="footer-bottom">
            <p>&copy; {{ now()->year }} Colegio Dante Alighieri. Aviso legal / Privacidad.</p>
            <div class="social-links">
                @if($facebookUrl)
                    <a href="{{ $facebookUrl }}" target="_blank" rel="noopener" aria-label="Facebook del Colegio Dante Alighieri">f</a>
                @endif
                @if($instagramUrl)
                    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" aria-label="Instagram del Colegio Dante Alighieri">ig</a>
                @endif
            </div>
        </div>
    </div>
</footer>
