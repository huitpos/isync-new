<x-default-layout>

    @section('title')
        Create a new printer
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('branch.printers.store', ['companySlug' => $company->slug, 'branchSlug' => $branch->slug]) }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="form-label">Name</label>
                    <input value="{{ old('name') }}" autocomplete="off" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Departments</label>
                    <select class="form-select" name="departments[]" data-control="select2" data-close-on-select="false" data-placeholder="Select department" data-allow-clear="true" multiple="multiple">
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>

                    @error('description')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>
                    

                <button type="submit" class="btn btn-primary mt-5 disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
