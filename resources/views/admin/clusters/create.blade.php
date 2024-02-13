<x-default-layout>

    @section('title')
        Create a new cluster
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('admin.clusters.create') }}
    @endsection

    <div class="card">
        <div class="card-body py-4">
            <form class="mt-3" action="{{ route('admin.clusters.store') }}" method="POST" novalidate enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="form-label">Company</label>
                    <select id="company_id" name="company_id" data-control="select2" data-placeholder="Select a company" class="form-control @error('company_id') is-invalid @enderror" required>
                        <option value=""></option>
                        @foreach ($companies as $company)`
                            <option value="{{ $company->id }}" {{ $company->id == old('company_id') ? 'selected' : '' }}>{{ $company->company_name }}</option>
                        @endforeach
                    </select>

                    @error('company_id')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

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
                    <label class="form-label">Name</label>
                    <input value="{{ old('name') }}" name="name" type="text" class="form-control @error('name') is-invalid @enderror" placeholder="Name" required/>

                    @error('name')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <input value="{{ old('description') }}" name="description" type="text" class="form-control @error('description') is-invalid @enderror" placeholder="Description" required/>

                    @error('description')
                        <div class="invalid-feedback"> {{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary disable-on-click">Submit</button>
                <a href="{{ url()->previous() }}" class="btn btn-label-secondary waves-effect">Cancel</a>
            </form>
        </div>
    </div>
</x-default-layout>
