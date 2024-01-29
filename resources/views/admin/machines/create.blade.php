<x-default-layout>

    @section('title')
        Create a new machine
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.machines.create', $branch) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('admin.machines.store', ['branchId' => $branch->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Device Name</label>
                    <input value="{{ old('name') }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Device Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Serial Number</label>
                    <input value="{{ old('serial_number') }}" name="serial_number" type="text" class="form-control @error('serial_number') is-invalid @enderror" placeholder="Serial Number" required/>

                    @error('serial_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Machine Identification Number</label>
                    <input value="{{ old('min') }}" name="min" type="text" class="form-control @error('min') is-invalid @enderror" placeholder="Machine Identification Number" required/>

                    @error('min')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Receipt Header</label>
                    <input value="{{ old('receipt_header') }}" name="receipt_header" type="text" class="form-control @error('receipt_header') is-invalid @enderror" placeholder="Receipt Header" required/>

                    @error('receipt_header')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Receipt Buttom Text</label>
                    <input value="{{ old('receipt_bottom_text') }}" name="receipt_bottom_text" type="text" class="form-control @error('receipt_bottom_text') is-invalid @enderror" placeholder="Receipt Buttom Text" required/>

                    @error('receipt_bottom_text')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Permit Number</label>
                    <input value="{{ old('permit_number') }}" name="permit_number" type="text" class="form-control @error('permit_number') is-invalid @enderror" placeholder="Permit Number" required/>

                    @error('permit_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Accreditation Number</label>
                    <input value="{{ old('accreditation_number') }}" name="accreditation_number" type="text" class="form-control @error('accreditation_number') is-invalid @enderror" placeholder="Accreditation Number" required/>

                    @error('accreditation_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Valid From</label>
                    <input value="{{ old('valid_from') }}" name="valid_from" type="text" class="form-control @error('valid_from') is-invalid @enderror flatpack-picker" placeholder="Valid From" required/>

                    @error('valid_from')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Valid Until</label>
                    <input value="{{ old('valid_to') }}" name="valid_to" type="text" class="form-control @error('valid_to') is-invalid @enderror flatpack-picker" placeholder="Valid Until" required/>

                    @error('valid_to')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">TIN</label>
                    <input value="{{ old('tin') }}" name="tin" type="text" class="form-control @error('tin') is-invalid @enderror" placeholder="TIN" required/>

                    @error('tin')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Cash Amount Limit</label>
                    <input value="{{ old('limit_amount') }}" name="limit_amount" type="text" class="form-control @error('limit_amount') is-invalid @enderror" placeholder="Cash Amount Limit" required/>

                    @error('limit_amount')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Vat</label>
                    <input value="{{ old('vat') }}" name="vat" type="text" class="form-control @error('vat') is-invalid @enderror" placeholder="Cash Amount Limit" required/>

                    @error('vat')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Machine Type</label>
                    <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="order station" {{ old('type') == 'order station' ? 'selected' : '' }}>Order Station</option>
                        <option value="cashier" {{ old('type') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                    </select>

                    @error('type')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
                <a href="{{ route('admin.clients.index') }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
