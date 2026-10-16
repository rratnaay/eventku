@props(['name'])
@error($name)
    <p class="field-error" role="alert">{{ $message }}</p>
@enderror
