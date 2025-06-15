<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Disposal</title>
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
        <h2>{{ $company->company_name }}</h2>
        @if(isset($branch))
        <p>
            {{ $branch->name }}<br>
            {{ $branch->unit_floor_number }},  {{ $branch->street }}, {{ $branch->city->name }}, {{ $branch->province->name }}, {{ $branch->region->name }}
        </p>
        @endif
        <h3>Product Disposal</h3>
    </div>

    <table>
        <tr>
            <td style="width:20%">Disposal #</td>
            <td>{{ $disposal->id }}</td>
            <td>Status</td>
            <td>{{ ucfirst($disposal->status) }}</td>
        </tr>
        <tr>
            <td>Requested By</td>
            <td>{{ $disposal->createdBy->name }}</td>
            <td>Department</td>
            <td>{{ $disposal->department->name }}</td>
        </tr>
        <tr>
            <td>Reason</td>
            <td colspan="3">{{ $disposal->productDisposalReason?->name }}</td>
        </tr>
    </table>

    <table class="product-table" style="margin-top:20px; margin-bottom:20px">
        <tr>
            <td style="width:20%">Remarks</td>
            <td>{{ $disposal->remarks }}</td>
        </tr>
    </table>

    <table class="product-table">
        <tr>
            <th style="width:40%">Product</th>
            <th>UOM</th>
            <th>Barcode</th>
            <th>Quantity</th>
        </tr>
        @php
            $total = 0;
        @endphp
        @foreach ($disposal->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->uom->name }}</td>
                <td>{{ $item->product->barcode }}</td>
                <td>{{ $item->quantity }}</td>
            </tr>
            @php
                $total += $item->quantity;
            @endphp
        @endforeach
        <tr>
            <td colspan="3" style="text-align: right;">Total Items</td>
            <td>{{ $total }}</td>
        </tr>
    </table>

    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="signature-line">{{ $disposal->createdBy->name }}</div>
                    <p>Requested By:</p>
                </td>
                @if($disposal->status != 'pending' && $disposal->actionBy)
                <td>
                    <div class="signature-line">{{ $disposal->actionBy->name }}</div>
                    <p>{{ $disposal->status == 'approved' ? 'Approved' : 'Rejected' }} By:</p>
                </td>
                @endif
            </tr>
        </table>
    </div>
</body>
</html>
