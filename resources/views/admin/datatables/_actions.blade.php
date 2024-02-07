@if (!isset($hideEdit))
    <a href="{{ route($route . '.edit', $param) }}" class="">
        <i class="fa-regular fa-pen-to-square fs-2" title="Edit"></i>
    </a>
@endif

@if (isset($showDelete) && $showDelete)
    <button data-url="{{ route($route . '.destroy', $deleteParam) }}" class="btn btn-icon btn-active-light-primary w-30px h-30px" data-permission-id="1" data-kt-action="delete_row" data-csrf={{ csrf_token() }}>
        <i class="fa-solid fa-trash fs-2 text-danger" title="Edit"></i>
    </button>
    
@endif