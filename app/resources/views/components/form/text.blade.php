@props(['name', 'label', 'type' => 'text', 'hint' => null, 'required' => false])
@php $error = $errors->first($name); @endphp
<div class="field @if($error) has-error @endif">
    <label for="{{ $name }}">{{ $label }}</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name) }}"
        @if($required) required aria-required="true" @endif
        @if($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes }}
    >
    @if($hint && !$error)
        <p class="hint">{{ $hint }}</p>
    @endif
    @if($error)
        <p class="msg-error" id="{{ $name }}-error" role="alert"><x-icon.alert-circle size="14" /> {{ $error }}</p>
    @endif
</div>
