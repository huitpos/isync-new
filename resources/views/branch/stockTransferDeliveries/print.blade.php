<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transfer Delivery</title>
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
        <h3>Stock Transfer Delivery</h3>
    </div>

    <table>
        <tr>
            <td style="width:20%">Control Number</td>
            <td>{{ $std->std_number }}</td>
            <td>Status</td>
            <td>{{ ucfirst($std->status)}}</td>
        </tr>
        @if ($std->status != 'pending')
        <tr>
            <td>Approved/Rejected By</td>
            <td colspan="3">{{ ucfirst($std->actionBy?->name) }}</td>
        </tr>
        @endif
        <tr>
            <td>Requested By</td>
            <td>{{ $std->createdBy->name }}</td>
            <td>Source Branch</td>
            <td>{{ $std->sourceBranch->name }}</td>
        </tr>
    </table>

    <table class="product-table" style="margin-top:20px; margin-bottom:20px">
        <tr>
            <th style="width:20%">Product</th>
            <th>UOM</th>
            <th>Barcode</th>
            <th>Quantity</th>
        </tr>
        @foreach ($std->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->uom->name }}</td>
                <td>{{ $item->product->barcode}}</td>
                <td>{{ $item->qty }}</td>
            </tr>
        @endforeach
    </table>

    <div class="remarks" style="margin-top:20px">
        <strong>Items Remarks:</strong>
        @foreach ($std->items as $item)
            @if($item->remarks)
                <p><strong>{{ $item->product->name }}:</strong> {{ $item->remarks }}</p>
            @endif
        @endforeach
    </div>

    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="signature-line">{{ $std->createdBy->name }}</div>
                    <p>Requested By:</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
