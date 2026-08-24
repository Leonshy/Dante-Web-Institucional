@props(['title' => null, 'description' => null, 'indexable' => true, 'canonical' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    {{-- Marca que JS está disponible antes de que el CSS decida ocultar contenido para
         animar su entrada — sin esta clase, `.reveal` nunca queda oculto, así el sitio
         nunca depende de JS para mostrar contenido real (docs/06-frontend.md §8 #4). --}}
    <script>document.documentElement.classList.add('js')</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Colegio Dante Alighieri' }}</title>
    <meta name="description" content="{{ $description ?? 'Colegio bilingüe español-italiano en Asunción, afiliado a la Società Dante Alighieri.' }}">
    @if(!($indexable ?? true))
        <meta name="robots" content="noindex">
    @endif
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    @fonts
    @vite(['resources/css/app.css'])
    @livewireStyles
</head>
<body>
<a class="skip-link" href="#contenido">Saltar al contenido principal</a>

<x-site-header />

{{ $slot }}

<x-site-footer />

@livewireScripts
@vite(['resources/js/app.js'])
@stack('scripts')
</body>
</html>
