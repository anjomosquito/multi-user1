<!DOCTYPE html>
<html>
<head>
    <title>Payment Report</title>
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
            grid-template-columns: repeat(3, 1fr);
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
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
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
        .status-verified {
            color: #2f855a;
        }
        .status-pending {
            color: #c05621;
        }
        .status-cancelled {
            color: #c53030;
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
        <h1>Payment Report</h1>
        <p>Period: {{ $start_date }} - {{ $end_date }}</p>
    </div>

    <div class="summary">
        <h2>Summary</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <strong>Total Transactions:</strong>
                <span>{{ number_format($summary['total_transactions']) }}</span>
            </div>
            <div class="summary-item">
                <strong>Total Amount:</strong>
                <span>₱{{ number_format($summary['total_amount'], 2) }}</span>
            </div>
            <div class="summary-item">
                <strong>Average Transaction:</strong>
                <span>₱{{ number_format($summary['average_transaction'], 2) }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <h2>Payment Status Summary</h2>
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
                @foreach($payment_data as $payment)
                <tr>
                    <td>
                        <span class="status-{{ strtolower($payment['status']) }}">
                            {{ ucfirst($payment['status']) }}
                        </span>
                    </td>
                    <td>{{ number_format($payment['count']) }}</td>
                    <td>₱{{ number_format($payment['total_amount'], 2) }}</td>
                    <td>₱{{ number_format($payment['average_amount'], 2) }}</td>
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
                    <th>Transactions</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment_trends as $trend)
                <tr>
                    <td>{{ $trend['date'] }}</td>
                    <td>
                        <span class="status-{{ strtolower($trend['status']) }}">
                            {{ ucfirst($trend['status']) }}
                        </span>
                    </td>
                    <td>{{ number_format($trend['count']) }}</td>
                    <td>₱{{ number_format($trend['total_amount'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
