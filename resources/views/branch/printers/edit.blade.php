<x-default-layout>

    @section('title')
        Update printer
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('branch.printers.update', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug, 'printer' => $printer->id]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="form-label">Status</label>
                    <select id="status" name="status" class="form-control @error('status') is-invalid @enderror" required>
                        <option value="active" {{ old('status') == 'active' || $printer->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' || $printer->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Name</label>
                    <input value="{{ old('name') ?? $printer->name }}" autocomplete="off" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Departments</label>
                    @php
                        $selectedDepartments = [];
                        foreach ($printer->departments as $department) {
                            $selectedDepartments[] = $department->id;
                        }
                    @endphp
                    <select class="form-select" name="departments[]" data-control="select2" data-close-on-select="false" data-placeholder="Select supplier" data-allow-clear="true" multiple="multiple">
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" {{ in_array($department->id, $selectedDepartments) ? 'selected' : '' }}>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                    

                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
