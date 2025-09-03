{{-- resources/views/branch/productDisposals/print.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Product Disposal #{{ $disposal->pdis_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .section { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company->name }}</h2>
        <h3>Product Disposal</h3>
        <p><strong>Disposal No:</strong> {{ $disposal->pdis_number }}<br>
        <strong>Date:</strong> {{ $disposal->created_at->format('Y-m-d') }}<br>
        <strong>Branch:</strong> {{ $branch->name }}</p>
    </div>

    <div class="section">
        <strong>Status:</strong> {{ ucfirst($disposal->status) }}<br>
        <strong>Requested By:</strong> {{ $disposal->createdBy->name }}<br>
        <strong>Department:</strong> {{ $disposal->department_id == 'all' || empty($disposal->department_id)) ? "All" : $disposal->department->name ?? '' }}<br>
        <strong>Reason:</strong> {{ $disposal->productDisposalReason?->name }}<br>
        <strong>Remarks:</strong> {{ $disposal->remarks }}
    </div>

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>UOM</th>
                    <th>Barcode</th>
                    <th>Quantity</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($disposal->items as $i => $item)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->uom->name }}</td>
                    <td>{{ $item->product->barcode }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->remarks }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
