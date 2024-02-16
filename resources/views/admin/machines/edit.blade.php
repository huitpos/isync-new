<x-default-layout>

    @section('title')
        Edit machine
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.machines.create', $branch) }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('admin.machines.update', ['branchId' => $branch->id, 'machine' => $machine->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" name="branch_id" value="{{ $branch->id }}">

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control form-select @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $machine->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $machine->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Device Name</label>
                    <input value="{{ old('name') ?? $machine->name }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Device Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Serial Number</label>
                    <input value="{{ old('serial_number') ?? $machine->serial_number }}" name="serial_number" type="text" class="form-control @error('serial_number') is-invalid @enderror" placeholder="Serial Number" required/>

                    @error('serial_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Machine Identification Number</label>
                    <input value="{{ old('min') ?? $machine->min }}" name="min" type="text" class="form-control @error('min') is-invalid @enderror" placeholder="Machine Identification Number" required/>

                    @error('min')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Receipt Header</label>
                    <input value="{{ old('receipt_header') ?? $machine->receipt_header }}" name="receipt_header" type="text" class="form-control @error('receipt_header') is-invalid @enderror" placeholder="Receipt Header" required/>

                    @error('receipt_header')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Receipt Buttom Text</label>
                    <input value="{{ old('receipt_bottom_text') ?? $machine->receipt_bottom_text }}" name="receipt_bottom_text" type="text" class="form-control @error('receipt_bottom_text') is-invalid @enderror" placeholder="Receipt Buttom Text" required/>

                    @error('receipt_bottom_text')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Permit Number</label>
                    <input value="{{ old('permit_number') ?? $machine->permit_number }}" name="permit_number" type="text" class="form-control @error('permit_number') is-invalid @enderror" placeholder="Permit Number" required/>

                    @error('permit_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Accreditation Number</label>
                    <input value="{{ old('accreditation_number') ?? $machine->accreditation_number }}" name="accreditation_number" type="text" class="form-control @error('accreditation_number') is-invalid @enderror" placeholder="Accreditation Number" required/>

                    @error('accreditation_number')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Valid From</label>
                    <input value="{{ old('valid_from') ?? $machine->valid_from }}" name="valid_from" type="text" class="form-control @error('valid_from') is-invalid @enderror flatpack-picker" placeholder="Valid From" required/>

                    @error('valid_from')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Valid Until</label>
                    <input value="{{ old('valid_to') ?? $machine->valid_to }}" name="valid_to" type="text" class="form-control @error('valid_to') is-invalid @enderror flatpack-picker" placeholder="Valid Until" required/>

                    @error('valid_to')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">TIN</label>
                    <input value="{{ old('tin') ?? $machine->tin }}" name="tin" type="text" class="form-control @error('tin') is-invalid @enderror" placeholder="TIN" required/>

                    @error('tin')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Cash Amount Limit</label>
                    <input value="{{ old('limit_amount') ?? $machine->limit_amount }}" name="limit_amount" type="text" class="form-control @error('limit_amount') is-invalid @enderror" placeholder="Cash Amount Limit" required/>

                    @error('limit_amount')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Vat</label>
                    <input value="{{ old('vat') ?? $machine->vat }}" name="vat" type="text" class="form-control @error('vat') is-invalid @enderror" placeholder="Cash Amount Limit" required/>

                    @error('vat')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Machine Type</label>
                    <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                        <option value="order station" {{ old('type') == 'order station' || $machine->type  == 'order station' ? 'selected' : '' }}>Order Station</option>
                        <option value="cashier" {{ old('type') == 'cashier' || $machine->type == 'cashier' ? 'selected' : '' }}>Cashier</option>
                    </select>

                    @error('type')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
