@props(['name', 'label', 'value' => '', 'type' => 'text', 'required' => false])
@php($inputValue = $type === 'password' ? '' : old($name, $value))
<div {{ $attributes->only('class') }}>
    <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    @if ($type === 'textarea')
        <textarea class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" rows="5" {{ $attributes->except('class') }} @required($required)>{{ is_scalar($inputValue) ? $inputValue : '' }}</textarea>
    @else
        <input class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ is_scalar($inputValue) ? $inputValue : '' }}" {{ $attributes->except('class') }} @required($required)>
    @endif
    @error($name)<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
