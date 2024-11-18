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
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report</h1>
        <div class="date-range">
            Period: {{ $start_date }} to {{ $end_date }}
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
                <td><strong>Total Items:</strong></td>
                <td>{{ $summary['total_items'] }}</td>
            </tr>
        </table>
    </div>

    <div class="section sales-list">
        <h2>Sales Details</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Medicine</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales_data as $sale)
                    <tr>
                        <td>{{ $sale['id'] }}</td>
                        <td>{{ $sale['user'] }}</td>
                        <td>{{ $sale['medicine_name'] }}</td>
                        <td>{{ $sale['quantity'] }}</td>
                        <td class="amount">₱{{ number_format($sale['price'], 2) }}</td>
                        <td class="amount">₱{{ number_format($sale['total_amount'], 2) }}</td>
                        <td>{{ $sale['payment_status'] }}</td>
                        <td>{{ $sale['created_at'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
