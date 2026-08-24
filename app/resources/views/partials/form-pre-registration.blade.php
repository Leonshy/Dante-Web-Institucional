@if(session('status'))
    <x-alert variant="success">{{ session('status') }}</x-alert>
@endif

<form class="form-card" method="POST" action="{{ route('forms.pre-registration') }}" aria-label="Formulario de pre-inscripción" novalidate>
    @csrf
    <x-honeypot />

    <h2 style="font-size:20px;margin-bottom:var(--spacing-4)">Pre-inscripción</h2>

    <x-form.text name="name" label="Nombre completo" autocomplete="name" required />
    <x-form.text name="email" label="Email" type="email" autocomplete="email" required />
    <x-form.text name="phone" label="Teléfono" type="tel" autocomplete="tel" required />
    <x-form.select name="site" label="Sede" required :options="['asuncion' => 'Asunción', 'fernando-de-la-mora' => 'Fernando de la Mora']" />
    <x-form.textarea name="message" label="Mensaje (opcional)" :required="false" />

    <p class="caption">La secretaría se comunica para coordinar la entrega de documentación.</p>
    <x-button variant="primary" type="submit" style="margin-top:var(--spacing-4)">Enviar pre-inscripción</x-button>
</form>
