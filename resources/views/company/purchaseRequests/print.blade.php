<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Request</title>
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
            {{ $pr->branch->name}}<br>
            {{ $branch->unit_floor_number }},  {{ $branch->street }}, {{ $branch->city->name }}, {{ $branch->province->name }}, {{ $branch->region->name }}
        </p>
        <h3>Purchase Request</h3>
    </div>

    <table>
        <tr>
            <td style="width:20%">PR#</td>
            <td>{{ $pr->pr_number }}</td>
            <td>Status</td>
            <td>{{ ucfirst($pr->status)}}</td>
        </tr>
        <tr>
            <td>Requested By</td>
            <td>{{ $pr->createdBy->first_name }} {{ $pr->createdBy->last_name }}</td>
            <td>Department</td>
            <td>{{ $pr->department_id == 'all' ? "All" : $pr->department->name }}</td>
        </tr>
        <tr>
            <td>Type</td>
            <td>For PR</td>
            <td>Date Needed</td>
            <td>{{ $pr->date_needed }}</td>
        </tr>
        <tr>
            <td>Delivery Location</td>
            <td>{{ $pr->deliveryLocation->name }}</td>
            <td>Supplier</td>
            <td>{{ $pr->supplier->name }}</td>
        </tr>
        <tr>
            <td>Delivery Address</td>
            <td colspan="3">{{ $pr->deliveryLocation->unit_floor_number }},  {{ $pr->deliveryLocation->street }}, {{ $pr->deliveryLocation->city->name }}, {{ $pr->deliveryLocation->province->name }}, {{ $pr->deliveryLocation->region->name }}</td>
        </tr>
        
    </table>

    <table class="product-table" style="margin-top:20px; margin-bottom:20px">
        <tr>
            <td style="width:20%">Remarks</td>
            <td>{{ $pr->remarks }}</td>
        </tr>
    </table>

    <table class="product-table">
        <tr>
            <th style="width:20%">Product</th>
            <th>UOM</th>
            <th>Barcode</th>
            <th>Unit Price</th>
            <th>Quantity</th>
            <th>Total</th>
        </tr>
        @php
            $total = 0;
        @endphp
        @foreach ($pr->items as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->uom->name }}</td>
                <td>{{ $item->product->barcode}}</td>
                <td>{{ number_format($item->unit_price, 2) }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->total, 2) }}</td>
            </tr>
            @php
                $total += $item->total;
            @endphp
        @endforeach
        <tr>
            <td colspan="5" style="text-align: right;">Total</td>
            <td>{{ number_format($total, 2) }}</td>
        </tr>
    </table>

    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="signature-line">{{ $pr->createdBy->first_name }} {{ $pr->createdBy->last_name }}</div>
                    <p>Requested By:</p>
                </td>
                <td>
                    <div class="signature-line">{{ $pr->actionBy->first_name }} {{ $pr->actionBy->last_name }}</div>
                    <p>Approved By:</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
