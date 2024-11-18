<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Sales Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .summary {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f5f5f5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .date-range {
            margin-bottom: 20px;
            font-style: italic;
        }
        .section {
            margin-bottom: 20px;
        }
        .amount {
            text-align: right;
        }
        .sales-list {
            margin-top: 20px;
        }
        .purchase-table {
            margin-left: 20px;
            width: 95%;
        }
        .transaction-header {
            background-color: #e9ecef;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>@if($single_purchase) Purchase Details @else Sales Report @endif</h1>
        <div class="date-range">
            @if($single_purchase)
                Date: {{ $start_date }}
            @else
                Period: {{ $start_date }} to {{ $end_date }}
            @endif
        </div>
    </div>

    <div class="section summary">
        <h2>Summary</h2>
        <table>
            <tr>
                <td><strong>Total Sales:</strong></td>
                <td class="amount">₱{{ number_format($summary['total_sales'], 2) }}</td>
                <td><strong>Total Orders:</strong></td>
                <td>{{ $summary['total_orders'] }}</td>
            </tr>
            <tr>
                <td><strong>Average Order Value:</strong></td>
                <td class="amount">₱{{ number_format($summary['average_order_value'], 2) }}</td>
                <td></td>
                <td></td>
            </tr>
        </table>
    </div>

    <div class="section sales-list">
        <h2>@if($single_purchase) Order Details @else Sales Details @endif</h2>
        @foreach($sales_data as $sale)
            <table>
                <tr class="transaction-header">
                    <td colspan="2">Order ID: {{ $sale['id'] }}</td>
                    <td colspan="2">Customer: {{ $sale['user'] }}</td>
                    <td colspan="2">Date: {{ $sale['created_at'] }}</td>
                    <td colspan="2">Status: {{ $sale['payment_status'] }}</td>
                </tr>
            </table>
            <table class="purchase-table">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $sale['medicine']['name'] }}</td>
                        <td>{{ $sale['medicine']['quantity'] }}</td>
                        <td class="amount">₱{{ number_format($sale['medicine']['price'], 2) }}</td>
                        <td class="amount">₱{{ number_format($sale['medicine']['subtotal'], 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="3" class="amount"><strong>Total Amount:</strong></td>
                        <td class="amount"><strong>₱{{ number_format($sale['total_amount'], 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>
            <br>
        @endforeach
    </div>
</body>
</html>
