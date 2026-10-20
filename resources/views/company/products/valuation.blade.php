<x-default-layout>
    @section('title')
        Inventory valuation
    @endsection

    <x-datatable-card :dataTable="$dataTable">
        <x-slot:title>
            FIFO value {{ number_format($total, 2) }}
        </x-slot:title>
        <p class="text-gray-600">Each row is the remaining quantity of the cost layers multiplied by the unit cost of those layers.</p>
    </x-datatable-card>
</x-default-layout>
