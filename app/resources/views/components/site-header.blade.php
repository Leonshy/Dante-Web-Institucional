@php
    $primaryNav = \App\Models\Menu::renderTree('primary');
    $italianEnabled = \App\Models\SiteSetting::italianEnabled();
@endphp
<div
    x-data="{
        mobileOpen: false,
        scrolled: false,
        open() {
            this.mobileOpen = true;
            this.$nextTick(() => this.$refs.mobileNav.querySelector('a, button')?.focus());
        },
        close() {
            this.mobileOpen = false;
            this.$refs.mobileToggle?.focus();
        },
        trapTab(event) {
            const focusables = this.$refs.mobileNav.querySelectorAll('a, button');
            if (!focusables.length) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
            else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
        },
    }"
    @scroll.window="scrolled = window.scrollY > 80"
>
    @if($italianEnabled)
        <div class="util-bar">
            <div class="container util-bar-inner">
                <div class="lang-toggle" role="group" aria-label="Cambiar idioma del sitio">
                    <a href="{{ route('locale.switch', 'es') }}" aria-current="{{ app()->getLocale() === 'es' ? 'true' : 'false' }}">ES</a>
                    <a href="{{ route('locale.switch', 'it') }}" aria-current="{{ app()->getLocale() === 'it' ? 'true' : 'false' }}">IT</a>
                </div>
                <a class="icon-btn" href="{{ route('search.index') }}" aria-label="Buscar en el sitio">
                    <x-icon.search />
                </a>
            </div>
        </div>
    @endif

    <header class="site-header" :class="{ 'is-scrolled': scrolled }">
        <div class="container site-header-inner">
            <a class="logo" href="{{ url('/') }}">
                <img src="{{ asset('images/logo-dante.svg') }}" alt="Colegio Dante Alighieri" width="211" height="90" class="logo-mark">
            </a>

            <nav aria-label="Principal">
                <ul class="desktop-nav">
                    @foreach($primaryNav as $item)
                        <li>
                            @if($item['linkable'] ?? true)
                                <a href="{{ url($item['url']) }}" @if(request()->is(ltrim($item['url'], '/')) || request()->is(ltrim($item['url'], '/').'/*')) aria-current="page" @endif>
                                    {{ $item['label'] }}
                                </a>
                            @else
                                {{-- Sin página propia — solo abre el submenú, no navega a ningún lado. --}}
                                <button type="button" class="nav-parent-toggle" aria-haspopup="true">{{ $item['label'] }}</button>
                            @endif
                            @if(!empty($item['children']))
                                <ul class="submenu">
                                    @foreach($item['children'] as $child)
                                        <li><a href="{{ url($child['url']) }}">{{ $child['label'] }}</a></li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </nav>

            @unless($italianEnabled)
                <a class="icon-btn" href="{{ route('search.index') }}" aria-label="Buscar en el sitio" style="margin-left:auto">
                    <x-icon.search />
                </a>
            @endunless

            <button type="button" class="icon-btn mobile-nav-toggle" aria-label="Abrir menú de navegación"
                    x-ref="mobileToggle"
                    :aria-expanded="mobileOpen.toString()" aria-controls="mobile-nav"
                    @click="open()">
                <x-icon.menu />
            </button>
        </div>
    </header>

    {{-- Hallazgo real de accesibilidad (Fase 9, E2E de teclado): el panel solo
         se desplazaba fuera de pantalla con `transform` al cerrarse, sin
         `display:none` ni equivalente — quedaba igual de "visible" para
         Playwright/lectores de pantalla y sus enlaces seguían en el orden de
         tabulación aunque estuvieran fuera de la vista. `inert` lo saca del
         árbol de accesibilidad y del tab order mientras está cerrado, sin
         tocar la animación de `transform` que ya existía. --}}
    <nav id="mobile-nav" class="mobile-nav" :class="{ 'is-open': mobileOpen }" aria-label="Principal"
         x-ref="mobileNav"
         :inert="!mobileOpen"
         @keydown.escape.window="close()"
         @keydown.tab="trapTab($event)">
        <div class="mobile-nav-header">
            <strong>DANTE</strong>
            <button type="button" class="icon-btn" aria-label="Cerrar menú de navegación" @click="close()">
                <x-icon.close />
            </button>
        </div>
        <ul class="mobile-nav-list">
            @foreach($primaryNav as $index => $item)
                @if(empty($item['children']))
                    <li><a href="{{ url($item['url']) }}">{{ $item['label'] }}</a></li>
                @else
                    <li x-data="{ open: false }">
                        <button type="button" class="mobile-submenu-toggle" :aria-expanded="open.toString()"
                                aria-controls="mobile-submenu-{{ $index }}" @click="open = !open">
                            {{ $item['label'] }}
                            <span class="chev" aria-hidden="true">
                                <x-icon.chevron-down />
                            </span>
                        </button>
                        <ul class="mobile-submenu" :class="{ 'is-open': open }" id="mobile-submenu-{{ $index }}">
                            @foreach($item['children'] as $child)
                                <li><a href="{{ url($child['url']) }}">{{ $child['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </li>
                @endif
            @endforeach
        </ul>
        <div class="mobile-nav-footer">
            <a class="btn btn-primary" href="{{ url('/admisiones') }}" style="width:100%">Quiero inscribir a mi hijo/a</a>
        </div>
    </nav>

    <div class="sticky-cta">
        <a class="btn btn-primary" href="{{ url('/admisiones') }}">Admisiones →</a>
    </div>
</div>
