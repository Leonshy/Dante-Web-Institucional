@php
    $contactEmail = \App\Models\SiteSetting::get('contact_email');
    $contactPhone = \App\Models\SiteSetting::get('contact_phone');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sitio en mantenimiento — Colegio Dante Alighieri</title>
    @fonts
    @vite(['resources/css/app.css'])
</head>
<body class="maintenance-body">
    <main class="maintenance-page">
        <img src="{{ asset('images/logo-dante.svg') }}" alt="Colegio Dante Alighieri" width="211" height="90" class="maintenance-logo">
        <span class="maintenance-accent" aria-hidden="true"></span>
        <h1>El sitio está en mantenimiento</h1>
        <p class="body-lg">
            Estamos actualizando el contenido del sitio. Vuelva a intentarlo en unos minutos.
        </p>
        @if($contactEmail || $contactPhone)
            <p class="maintenance-contact">
                Si necesita comunicarse con el colegio mientras tanto:
                @if($contactEmail)
                    <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
                @endif
                @if($contactEmail && $contactPhone)
                    ·
                @endif
                @if($contactPhone)
                    <a href="tel:{{ $contactPhone }}">{{ $contactPhone }}</a>
                @endif
            </p>
        @endif
    </main>
</body>
</html>
