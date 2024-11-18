<!DOCTYPE html>
<html>
<head>
    <title>Purchase Receipt - {{ $purchase['transaction_number'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .details {
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f4f4f4;
        }
        .total {
            text-align: right;
            margin-top: 20px;
            font-weight: bold;
        }
        .transaction-number {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .peso-sign {
            font-family: DejaVu Sans, sans-serif;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Official Receipt</h1>
        <div class="transaction-number">Transaction #: {{ $purchase['transaction_number'] }}</div>
    </div>

    <div class="details">
        <p><strong>Date:</strong> {{ $purchase['date'] }}</p>
        <p><strong>Customer:</strong> {{ $purchase['customer_name'] }}</p>
        <p><strong>Status:</strong> {{ ucfirst($purchase['status']) }}</p>
        <p><strong>Payment Status:</strong> {{ ucfirst($purchase['payment_status']) }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Medicine</th>
                <th>Dosage</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $purchase['medicine_name'] }}</td>
                <td>{{ $purchase['dosage'] }}</td>
                <td>{{ $purchase['quantity'] }}</td>
                <td><span class="peso-sign">₱</span>{{ $purchase['unit_price'] }}</td>
                <td><span class="peso-sign">₱</span>{{ $purchase['total_amount'] }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        <p>Total Amount: <span class="peso-sign">₱</span>{{ $purchase['total_amount'] }}</p>
    </div>
</body>
</html> 