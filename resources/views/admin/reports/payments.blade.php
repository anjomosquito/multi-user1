<!DOCTYPE html>
<html>
<head>
    <title>Admin Payment Report</title>
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
            grid-template-columns: repeat(2, 1fr);
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
        .status-colors {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .status-box {
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .pending { background-color: #fff3cd; }
        .completed { background-color: #d4edda; }
        .failed { background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admin Payment Report</h1>
        <div class="date-range">
            Period: {{ $start_date }} to {{ $end_date }}
        </div>
    </div>

    <div class="summary">
        <h2 class="section-title">Payment Status Summary</h2>
        <div class="status-colors">
            @foreach($payment_data as $status)
                <div class="status-box {{ strtolower($status->payment_status) }}">
                    <h3>{{ $status->payment_status }}</h3>
                    <p>Count: {{ $status->count }}</p>
                    <p>Total: ₱{{ number_format($status->total_amount, 2) }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <h2 class="section-title">Payment Trends by Date</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Status</th>
                <th>Number of Transactions</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payment_trends as $trend)
                <tr>
                    <td>{{ $trend->date }}</td>
                    <td>{{ $trend->payment_status }}</td>
                    <td>{{ $trend->count }}</td>
                    <td>₱{{ number_format($trend->total_amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h2 class="section-title">Overall Statistics</h2>
        <div class="stats-grid">
            <div class="stat-box">
                <h3>Total Transactions</h3>
                <p>{{ $payment_trends->sum('count') }}</p>
            </div>
            <div class="stat-box">
                <h3>Total Amount</h3>
                <p>₱{{ number_format($payment_trends->sum('total_amount'), 2) }}</p>
            </div>
        </div>
    </div>
</body>
</html>
