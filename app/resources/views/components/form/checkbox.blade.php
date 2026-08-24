@props(['name', 'label', 'required' => false])
@php $error = $errors->first($name); @endphp
<div class="field-checkbox">
    <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1" @checked(old($name))
        @if($required) required aria-required="true" @endif
        @if($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes }}>
    <label for="{{ $name }}" style="margin:0;font-weight:400">{{ $label }}</label>
    @if($error)
        <p class="msg-error" id="{{ $name }}-error" role="alert"><x-icon.alert-circle size="14" /> {{ $error }}</p>
    @endif
</div>
