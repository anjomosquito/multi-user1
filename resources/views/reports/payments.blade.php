<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Payment Report</title>
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
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .status-box {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .verified { background-color: #d4edda; }
        .pending { background-color: #fff3cd; }
        .cancelled { background-color: #f8d7da; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Payment Report</h1>
        <div class="date-range">
            Period: {{ $start_date }} to {{ $end_date }}
        </div>
    </div>

    <div class="section summary">
        <h2>Summary Statistics</h2>
        <table>
            <tr>
                <td><strong>Total Transactions:</strong></td>
                <td>{{ number_format($summary['total_transactions']) }}</td>
                <td><strong>Total Amount:</strong></td>
                <td class="amount">₱{{ number_format($summary['total_amount'], 2) }}</td>
            </tr>
            <tr>
                <td><strong>Average Transaction:</strong></td>
                <td class="amount">₱{{ number_format($summary['average_transaction'], 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Payment Status Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Total Amount</th>
                    <th>Average Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment_data as $data)
                    <tr>
                        <td>{{ $data['status'] }}</td>
                        <td>{{ number_format($data['count']) }}</td>
                        <td class="amount">₱{{ number_format($data['total_amount'], 2) }}</td>
                        <td class="amount">₱{{ number_format($data['average_amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Daily Payment Trends</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Count</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment_trends as $trend)
                    <tr>
                        <td>{{ $trend['date'] }}</td>
                        <td>{{ $trend['status'] }}</td>
                        <td>{{ number_format($trend['count']) }}</td>
                        <td class="amount">₱{{ number_format($trend['total_amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
