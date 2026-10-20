<x-default-layout>

    @section('title')
        Account Receivables Report
    @endsection

    <div class="card" data-dt-card>
        <div class="card-body py-4">
            <form class="mt-3" method="POST" novalidate>
                @csrf
            </form>

            <div class="table-responsive">
                {{ $dataTable->table() }}
            </div>
        </div>
    </div>
    @push('scripts')
        {{ $dataTable->scripts() }}
    @endpush
</x-default-layout>