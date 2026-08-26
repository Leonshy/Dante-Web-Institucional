@if(session('status'))
    <x-alert variant="success">{{ session('status') }}</x-alert>
@endif
@if(session('form_error'))
    <x-alert variant="danger">{{ session('form_error') }}</x-alert>
@endif

<form class="form-card" method="POST" action="{{ route('forms.contact') }}" aria-label="Formulario de contacto" novalidate>
    @csrf
    <x-honeypot />

    <h2 style="font-size:20px;margin-bottom:var(--spacing-4)">Escríbanos</h2>

    <x-form.text name="name" label="Nombre completo" autocomplete="name" required />
    <x-form.text name="phone" label="Teléfono" type="tel" autocomplete="tel" />
    <x-form.text name="email" label="Email" type="email" autocomplete="email" required />
    <x-form.textarea name="message" label="Mensaje" required />

    <x-turnstile-widget />

    <p class="caption">Protegido con verificación anti-robots. Al enviar, acepta el tratamiento de sus datos según el aviso de privacidad.</p>
    <x-button variant="primary" type="submit" style="margin-top:var(--spacing-4)">Enviar mensaje</x-button>
</form>
