<x-default-layout>

    @section('title')
        Edit product disposal reason
    @endsection

    @section('breadcrumbs')
        {{-- {{ Breadcrumbs::render('company.supplierTerms.create', $company) }} --}}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.product-disposal-reasons.update', ['companySlug' => $company->slug, 'product_disposal_reason' => $reason->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Reason</label>
                    <input value="{{ old('name') ?? $reason->name }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Reason" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
