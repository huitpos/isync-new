<x-default-layout>
    @section('title')
        Low stock
    @endsection

    <x-datatable-card :dataTable="$dataTable">
        <x-slot:title>
            At or below minimum stock
        </x-slot:title>
        <p class="text-gray-600">Minimum stock is the reorder point on the product. The quantity shown is the branch stock the POS uses.</p>
    </x-datatable-card>
</x-default-layout>
