@props(['data'])
<div class="section">
    <div class="container" style="max-width:640px">
        @if(($data['form_type'] ?? 'contacto') === 'preinscripcion')
            @include('partials.form-pre-registration')
        @else
            @include('partials.form-contact')
        @endif
    </div>
</div>
