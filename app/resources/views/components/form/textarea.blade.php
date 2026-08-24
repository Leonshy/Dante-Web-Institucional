@props(['name', 'label', 'hint' => null, 'required' => false, 'rows' => 5])
@php $error = $errors->first($name); @endphp
<div class="field @if($error) has-error @endif">
    <label for="{{ $name }}">{{ $label }}</label>
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if($required) required aria-required="true" @endif
        @if($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes }}
    >{{ old($name) }}</textarea>
    @if($hint && !$error)
        <p class="hint">{{ $hint }}</p>
    @endif
    @if($error)
        <p class="msg-error" id="{{ $name }}-error" role="alert"><x-icon.alert-circle size="14" /> {{ $error }}</p>
    @endif
</div>
