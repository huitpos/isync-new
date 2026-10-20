<x-default-layout>

    @section('title')
        Products
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.products.index', $company) }}
    @endsection

    @if($errors->all())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if (session('import_preview'))
        @php $preview = session('import_preview'); @endphp
        <div class="alert alert-warning">
            <div class="fw-bold mb-2">Review this file before importing</div>
            <div>{{ $preview['rows'] }} data row(s). Blank names: {{ $preview['blank_names'] }}. Duplicate names in the file: {{ $preview['duplicate_names'] }}. Duplicate barcodes in the file: {{ $preview['duplicate_barcodes'] }}. Names already in the catalog: {{ $preview['existing_names'] }}. Barcodes already in the catalog: {{ $preview['existing_barcodes'] }}.</div>
            @if (!empty($preview['issues']))
                <ul class="mb-3 mt-3">
                    @foreach ($preview['issues'] as $issue)
                        <li>{{ $issue }}</li>
                    @endforeach
                </ul>
            @endif
            <form method="POST" action="{{ route('company.products.import', ['companySlug' => $company->slug]) }}" class="mt-3" onsubmit="return confirm('Start the import for {{ $preview['rows'] }} row(s)? Existing names may be updated.');">
                @csrf
                <input type="hidden" name="confirm" value="1">
                <input type="hidden" name="stored_path" value="{{ session('import_path') }}">
                <button type="submit" class="btn btn-primary">Import these rows</button>
            </form>
        </div>
    @endif

    <div class="card" data-dt-card>
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                Products
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                    <a href="{{ route('company.products.export', ['companySlug' => $company->slug]) }}" class="btn btn-light-primary me-3">
                        Export Products
                    </a>

                    @if (in_array('Settings/Products/Import Products', $permissions))
                    <button type="button" class="btn btn-primary me-3" data-bs-toggle="modal" data-bs-target="#kt_modal_1">
                        Import Products
                    </button>
                    @endif

                    @if (in_array('Settings/Products/Add', $permissions))
                    <a href="{{ route('company.products.create', ['companySlug' => $company->slug]) }}" class="btn btn-primary">
                        {!! getIcon('plus', 'fs-2', '', 'i') !!}
                        Add Product
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>

    @push('scripts')
        {{ $dataTable->scripts() }}
        
    @endpush

</x-default-layout>

<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <form action="{{ route('company.products.import', ['companySlug' => $company->slug]) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Import products</h3>

                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <input type="file" name="file" accept=".xlsx" required>
                    <div class="form-text mt-3">Check the file first. You will see the row count, blank names, and duplicates before anything is imported.</div>
                    <a href="/download-product-import-template">Download template</a>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary disable-on-click">Check file</button>
                </div>
            </div>
        </form>
    </div>
</div>