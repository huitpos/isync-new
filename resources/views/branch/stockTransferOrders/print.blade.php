<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transfer Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border: 1px solid black;
            text-align: left;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .remarks, .signatures {
            margin-top: 20px;
        }
        .signatures td {
            border: none;
            padding-top: 40px;
            text-align: center;
        }
        .signature-line {
            width: 50%;
            border-bottom: 1px solid black;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $company->name }}</h2>
        <p>{{ $branch->name }}</p>
        <h3>Stock Transfer Order</h3>
    </div>

    <table>
        <tr>
            <td style="width:20%">STO#</td>
            <td>{{ $sto->sto_number }}</td>
            <td>Status</td>
            <td>{{ ucfirst($sto->status)}}</td>
        </tr>
        @if ($sto->status != 'pending')
        <tr>
            <td>Approved/Rejected By</td>
            <td colspan="3">{{ ucfirst($sto->actionBy?->name) }}</td>
        </tr>
        @endif
        <tr>
            <td>Requested By</td>
            <td>{{ $sto->createdBy->name }}</td>
            <td>Department</td>
            <td>{{ ($sto->department_id == 'all' || empty($sto->department_id)) ? "All" : $sto->department->name }}</td>
        </tr>
        <tr>
            <td>Delivery Location</td>
            <td>{{ $sto->deliveryLocation->name }}</td>
            <td>Source Branch</td>
            <td>{{ $sto->sourceBranch->name }}</td>
        </tr>
        <tr>
            <td>Delivery Address</td>
            <td colspan="3">{{ $sto->deliveryLocation->unit_floor_number }}, {{ $sto->deliveryLocation->street }}, {{ $sto->deliveryLocation->barangay->name }}, {{ $sto->deliveryLocation->city->name }}, {{ $sto->deliveryLocation->province->name }}, {{ $sto->deliveryLocation->region->name }}</td>
        </tr>
        
    </table>

    <table class="product-table" style="margin-top:20px; margin-bottom:20px">
        <tr>
            <td style="width:20%">Remarks</td>
            <td>{{ $sto->remarks }}</td>
        </tr>
    </table>

    <table class="product-table">
        <tr>
            <th style="width:20%">Product</th>
            <th>UOM</th>
            <th>Barcode</th>
            <th>Cost</th>
            <th>Quantity</th>
        </tr>
        @foreach ($sto->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->uom->name }}</td>
                <td>{{ $item->product->barcode}}</td>
                <td>{{ number_format($item->product->cost, 2) }}</td>
                <td>{{ $item->quantity }}</td>
            </tr>
        @endforeach
    </table>

    <div class="remarks" style="margin-top:20px">
        <strong>Items Remarks:</strong>
        @foreach ($sto->items as $item)
            @if($item->remarks)
                <p><strong>{{ $item->product->name }}:</strong> {{ $item->remarks }}</p>
            @endif
        @endforeach
    </div>

    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="signature-line">{{ $sto->createdBy->name }}</div>
                    <p>Requested By:</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
