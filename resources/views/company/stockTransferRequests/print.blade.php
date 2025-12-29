<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transfer Request</title>
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
        <p>
            {{ $str->sourceBranch->name}}<br>
            {{ $str->sourceBranch->unit_floor_number }},  {{ $str->sourceBranch->street }}, {{ $str->sourceBranch->city->name }}, {{ $str->sourceBranch->province->name }}, {{ $str->sourceBranch->region->name }}
        </p>
        <h3>Stock Transfer Request</h3>
    </div>

    <table>
        <tr>
            <td style="width:20%">STR#</td>
            <td>{{ $str->str_number }}</td>
            <td>Status</td>
            <td>{{ ucfirst($str->status) }}</td>
        </tr>
        <tr>
            <td>Requested By</td>
            <td>{{ $str->createdBy->name }}</td>
            <td>Department</td>
            <td>{{ $str->department->name }}</td>
        </tr>
        <tr>
            <td>Source Branch</td>
            <td>{{ $str->sourceBranch->name }}</td>
            <td>Delivery Location</td>
            <td>{{ $str->deliveryLocation->name }}</td>
        </tr>
        <tr>
            <td>Delivery Address</td>
            <td colspan="3">{{ $str->deliveryLocation->unit_floor_number }}, {{ $str->deliveryLocation->street }}, {{ $str->deliveryLocation->barangay->name }}, {{ $str->deliveryLocation->city->name }}, {{ $str->deliveryLocation->province->name }}, {{ $str->deliveryLocation->region->name }}</td>
        </tr>
    </table>

    <table class="product-table" style="margin-top:20px; margin-bottom:20px">
        <tr>
            <td style="width:20%">Remarks</td>
            <td>{{ $str->remarks }}</td>
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
        @foreach ($str->items as $item)
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
                    <div class="signature-line">{{ $str->createdBy->name }}</div>
                    <p>Requested By:</p>
                </td>
                @if($str->status != 'pending' && $str->actionBy)
                <td>
                    <div class="signature-line">{{ $str->actionBy->name }}</div>
                    <p>{{ $str->status == 'approved' ? 'Approved' : 'Rejected' }} By:</p>
                </td>
                @endif
            </tr>
        </table>
    </div>
</body>
</html>
