<x-default-layout>

    @section('title')
        Create a new supplier
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.suppliers.create', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.suppliers.store', ['companySlug' => $company->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Supplier Name</label>
                    <input value="{{ old('name') }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Supplier Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Contact Person</label>
                    <input value="{{ old('contact_person') }}" name="contact_person" type="text" class="form-control @error('contact_person') is-invalid @enderror" placeholder="Contact Person" required/>

                    @error('contact_person')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Contact Number</label>
                    <input value="{{ old('contact_number') }}" name="contact_number" type="text" class="form-control @error('contact_number') is-invalid @enderror" placeholder="Contact Number" required/>

                    @error('contact_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Email Address</label>
                    <input value="{{ old('email') }}" name="email" type="text" class="form-control @error('email') is-invalid @enderror" placeholder="Email Address" required/>

                    @error('email')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Address</label>
                    <input value="{{ old('address') }}" name="address" type="text" class="form-control @error('address') is-invalid @enderror" placeholder="Address" required/>

                    @error('address')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                
                <button type="submit" class="btn btn-primary mt-5">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
