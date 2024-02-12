<x-default-layout>

    @section('title')
        Edit charge account
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.chargeAccounts.edit', $company) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('company.charge-accounts.update', ['companySlug' => $company->slug, 'charge_account' => $chargeAccount->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $chargeAccount->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $chargeAccount->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Account Name</label>
                    <input value="{{ old('name') ?? $chargeAccount->name }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Account Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Address</label>
                    <input value="{{ old('address') ?? $chargeAccount->address }}" name="address" type="text" class="form-control @error('address') is-invalid @enderror" placeholder="Address" required/>

                    @error('address')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Contact No.</label>
                    <input value="{{ old('contact_number') ?? $chargeAccount->contact_number }}" name="contact_number" type="text" class="form-control @error('contact_number') is-invalid @enderror" placeholder="Contact Number" required/>

                    @error('contact_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Email Address</label>
                    <input value="{{ old('email') ?? $chargeAccount->email }}" name="email" type="text" class="form-control @error('email') is-invalid @enderror" placeholder="Email Address" required/>

                    @error('email')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Credit Limit</label>
                    <input value="{{ old('credit_limit') ?? $chargeAccount->credit_limit }}" name="credit_limit" type="text" class="form-control @error('credit_limit') is-invalid @enderror" placeholder="Credit Limit" required/>

                    @error('credit_limit')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary mt-5">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
