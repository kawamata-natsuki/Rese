@props(['field', 'class' => ''])

@error($field)
<div class="error-message error-message--active {{ $class }}">
  {{ $message }}
</div>
@else
<div class="error-message {{ $class }}">
  &nbsp;
</div>
@enderror