<x-default-layout>

    @section('title')
        Debit Memos
    @endsection

    @section('breadcrumbs')
        {{ Breadcrumbs::render('company.debitMemos.index', $company) }}
    @endsection

    <x-datatable-card :dataTable="$dataTable">
        <x-slot:filters>
            <select id="branch_id" class="form-select form-select-solid w-200px" data-dt-filter="branch_id">
                <option value="">Branch</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            <select id="status" class="form-select form-select-solid w-150px" data-dt-filter="status">
                <option value="">Status</option>
                <option value="issued">Issued</option>
                <option value="void">Void</option>
            </select>
        </x-slot:filters>
        <x-slot:actions>
            <a href="{{ route('company.debit-memos.create', ['companySlug' => $company->slug]) }}" class="btn btn-primary">Add Debit Memo</a>
        </x-slot:actions>
    </x-datatable-card>

</x-default-layout>
