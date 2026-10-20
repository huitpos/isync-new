<x-default-layout>

    @section('title')
        Products
    @endsection

    @section('breadcrumbs')
        {{-- {{ Breadcrumbs::render('company.products.index', $company) }} --}}
    @endsection

    @if($errors->all())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="card" data-dt-card>
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                
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