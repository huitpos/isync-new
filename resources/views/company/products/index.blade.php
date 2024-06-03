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

    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    {!! getIcon('magnifier', 'fs-3 position-absolute ms-5') !!}
                    <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search" id="searchBar"/>
                </div>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
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
        <script>
            document.getElementById('searchBar').addEventListener('keyup', function () {
                window.LaravelDataTables['clusters-table'].search(this.value).draw();
            });
        </script>
    @endpush

</x-default-layout>

<div class="modal fade" tabindex="-1" id="kt_modal_1">
    <div class="modal-dialog">
        <form action="{{ route('company.products.import', ['companySlug' => $company->slug]) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"></h3>

                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                    </div>
                    <!--end::Close-->
                </div>

                <div class="modal-body">
                    <input type="file" name="file">

                    <a href="/download-product-import-template">Download template</a>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary disable-on-click">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>