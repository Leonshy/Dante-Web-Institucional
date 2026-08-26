@php
    $integrations = \App\Models\IntegrationSetting::current();
@endphp
@if($integrations->turnstileActive())
    <div class="cf-turnstile" data-sitekey="{{ $integrations->turnstile_site_key }}" data-theme="light"></div>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
