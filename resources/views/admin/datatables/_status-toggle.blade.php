<div class="form-check form-switch form-check-custom form-check">
    <input data-action="{{ route($route, $param) }}" data-csrf="{{ csrf_token() }}" class="form-check-input h-30px w-60px status-toggle" type="checkbox" {{ $checked }}/>
</div>