<x-default-layout>
    @section('title')
        Inventory valuation
    @endsection

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">FIFO value {{ number_format($total, 2) }}</h2>
        </div>
        <div class="card-body">
            <p class="text-gray-600">Each row is the remaining quantity of the cost layers multiplied by the unit cost of those layers.</p>
            <table class="table table-row-dashed">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th class="text-end">Remaining qty</th>
                        <th class="text-end">Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row->branch_name }}</td>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->sku }}</td>
                            <td class="text-end">{{ number_format($row->qty, 2) }}</td>
                            <td class="text-end">{{ number_format($row->value, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No remaining cost layers.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-default-layout>
