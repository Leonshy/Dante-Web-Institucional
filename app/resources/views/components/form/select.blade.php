@props(['name', 'label', 'options' => [], 'required' => false, 'selected' => null])
@php
    $error = $errors->first($name);
    $current = $selected ?? old($name);
@endphp
<div class="field @if($error) has-error @endif">
    <label for="{{ $name }}">{{ $label }}</label>
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if($required) required aria-required="true" @endif
        @if($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        {{ $attributes }}
    >
        @foreach($options as $value => $label)
            <option value="{{ $value }}" @selected($current === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @if($error)
        <p class="msg-error" id="{{ $name }}-error" role="alert"><x-icon.alert-circle size="14" /> {{ $error }}</p>
    @endif
</div>
