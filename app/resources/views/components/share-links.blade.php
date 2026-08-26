@props(['title', 'url' => null])
@php
    // Enlaces de intención de compartir (share intent), sin SDK de terceros
    // ni pixel de tracking — nada que cargar hasta que la persona haga clic
    // en un enlace real hacia la red social.
    $shareUrl = $url ?? request()->url();
    $encodedTitle = rawurlencode($title);
    $encodedUrl = rawurlencode($shareUrl);
@endphp
<div class="share-links" role="group" aria-label="Compartir esta noticia">
    <span class="share-links-label">Compartir</span>
    <a class="btn btn-secondary share-link" href="https://wa.me/?text={{ $encodedTitle }}%20{{ $encodedUrl }}" target="_blank" rel="noopener noreferrer">
        <x-icon.whatsapp size="18" /> WhatsApp
    </a>
    <a class="btn btn-secondary share-link" href="https://twitter.com/intent/tweet?text={{ $encodedTitle }}&url={{ $encodedUrl }}" target="_blank" rel="noopener noreferrer">
        <x-icon.x-twitter size="18" /> X
    </a>
    <a class="btn btn-secondary share-link" href="https://www.facebook.com/sharer/sharer.php?u={{ $encodedUrl }}" target="_blank" rel="noopener noreferrer">
        <x-icon.facebook size="18" /> Facebook
    </a>
</div>
