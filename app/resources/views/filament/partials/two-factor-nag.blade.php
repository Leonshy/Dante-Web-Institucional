{{--
    Alerta persistente (ADR-003): se muestra en cada página del panel mientras
    el usuario autenticado no activó el 2FA por email. No es descartable a
    propósito — el 2FA es opt-in, así que esta alerta es la única presión que
    reemplaza la obligatoriedad de antes.
--}}
<div class="fi-two-factor-nag" style="background:#fef3c7;border-bottom:1px solid #f59e0b;padding:0.5rem 1rem;text-align:center;font-size:0.875rem;color:#78350f">
    Tu cuenta no tiene activada la verificación en dos pasos.
    <a href="{{ \Filament\Facades\Filament::getProfileUrl() }}" style="text-decoration:underline;font-weight:600">Activarla ahora</a>
</div>
