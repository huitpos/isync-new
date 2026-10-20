<div class="card" data-dt-card>
    @if (!empty($alert))
        <div class="alert alert-primary m-6 mb-0">{{ $alert }}</div>
    @endif

    @if (isset($title) || isset($filters) || isset($actions))
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title">
                {{ $title ?? '' }}
            </div>
            <div class="card-toolbar flex-row-fluid justify-content-end gap-3 flex-wrap">
                {{ $filters ?? '' }}
                {{ $actions ?? '' }}
            </div>
        </div>
    @endif

    <div class="card-body py-4">
        {{ $slot }}
        <div class="table-responsive">
            {{ $dataTable->table() }}
        </div>
    </div>
</div>

@push('scripts')
    {{ $dataTable->scripts() }}
@endpush
