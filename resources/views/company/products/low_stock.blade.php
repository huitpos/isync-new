<x-default-layout>
    @section('title')
        Low stock
    @endsection

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">At or below minimum stock</h2>
        </div>
        <div class="card-body">
            <p class="text-gray-600">Minimum stock is the reorder point on the product. The quantity shown is the branch stock the POS uses.</p>
            <table class="table table-row-dashed">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th class="text-end">On hand</th>
                        <th class="text-end">Minimum</th>
                        <th class="text-end">FIFO cost</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row->branch_name }}</td>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->sku }}</td>
                            <td class="text-end">{{ number_format($row->stock, 2) }}</td>
                            <td class="text-end">{{ number_format($row->minimum_stock_level, 2) }}</td>
                            <td class="text-end">{{ number_format($row->cost ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No branch is at or below its minimum stock.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-default-layout>
