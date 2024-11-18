<!DOCTYPE html>
<html>
<head>
    <title>Admin Sales Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
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
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .date-range {
            margin-bottom: 20px;
            font-style: italic;
        }
        .section-title {
            margin-top: 30px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }
        .top-products {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Sales Report</h1>
        <div class="date-range">
            Period: {{ $start_date }} to {{ $end_date }}
        </div>
    </div>

    <div class="summary">
        <h2>Summary</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <h3>Total Sales</h3>
                <p>₱{{ number_format($summary['total_sales'], 2) }}</p>
            </div>
            <div class="stat-box">
                <h3>Total Orders</h3>
                <p>{{ $summary['total_orders'] }}</p>
            </div>
            <div class="stat-box">
                <h3>Average Order Value</h3>
                <p>₱{{ number_format($summary['average_order_value'], 2) }}</p>
            </div>
        </div>
    </div>

    <div class="additional-stats">
        <h2 class="section-title">Additional Statistics</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <h3>Total Customers</h3>
                <p>{{ $additional_stats['total_customers'] }}</p>
            </div>
            <div class="stat-box">
                <h3>Total Products</h3>
                <p>{{ $additional_stats['total_products'] }}</p>
            </div>
        </div>
    </div>

    <div class="top-products">
        <h2 class="section-title">Top Selling Products</h2>
        <table>
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Total Quantity Sold</th>
                </tr>
            </thead>
            <tbody>
                @foreach($additional_stats['top_selling_products'] as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->total_quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <h2 class="section-title">Sales Details</h2>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Total Amount</th>
                <th>Payment Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales_data as $sale)
                <tr>
                    <td>#{{ $sale['id'] }}</td>
                    <td>{{ $sale['user'] }}</td>
                    <td>₱{{ number_format($sale['total_amount'], 2) }}</td>
                    <td>{{ $sale['payment_status'] }}</td>
                    <td>{{ $sale['created_at'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
