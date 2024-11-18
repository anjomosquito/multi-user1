<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #2d3748;
        }
        .header p {
            color: #4a5568;
            margin: 5px 0;
        }
        .summary {
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
            padding: 15px;
            border-radius: 8px;
        }
        .summary h2 {
            margin-top: 0;
            color: #2d3748;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .summary-item {
            padding: 10px;
            background-color: #f7fafc;
            border-radius: 4px;
        }
        .summary-item strong {
            color: #2d3748;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f7fafc;
            color: #2d3748;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f7fafc;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales Report</h1>
        <p>Period: {{ $start_date }} - {{ $end_date }}</p>
    </div>

    <div class="summary">
        <h2>Summary</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <strong>Total Sales:</strong>
                <span>₱{{ number_format($summary['total_sales'], 2) }}</span>
            </div>
            <div class="summary-item">
                <strong>Total Orders:</strong>
                <span>{{ number_format($summary['total_orders']) }}</span>
            </div>
            <div class="summary-item">
                <strong>Average Order Value:</strong>
                <span>₱{{ number_format($summary['average_order_value'], 2) }}</span>
            </div>
            <div class="summary-item">
                <strong>Total Items Sold:</strong>
                <span>{{ number_format($summary['total_items']) }}</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Medicine</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales_data as $sale)
            <tr>
                <td>{{ $sale['id'] }}</td>
                <td>{{ $sale['user'] }}</td>
                <td>{{ $sale['medicine_name'] }}</td>
                <td>{{ number_format($sale['quantity']) }}</td>
                <td>₱{{ number_format($sale['price'], 2) }}</td>
                <td>₱{{ number_format($sale['total_amount'], 2) }}</td>
                <td>{{ $sale['created_at'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
