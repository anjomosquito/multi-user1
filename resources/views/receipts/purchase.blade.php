<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Purchase Receipt</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .receipt {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .store-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .store-address {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        .receipt-details {
            margin-bottom: 20px;
        }
        .receipt-no {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .amount {
            text-align: right;
        }
        .total {
            font-weight: bold;
            font-size: 14px;
            border-top: 2px solid #000;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
        }
        .payment-status {
            text-align: center;
            padding: 5px;
            margin: 10px 0;
            font-weight: bold;
        }
        .payment-pending {
            color: #856404;
            background-color: #fff3cd;
        }
        .payment-verified {
            color: #155724;
            background-color: #d4edda;
        }
        .payment-rejected {
            color: #721c24;
            background-color: #f8d7da;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="header">
            <div class="store-name">PUREMED PHARMACY</div>
            <div class="store-address">123 Healthcare Street, Medical District</div>
            <div class="store-address">Contact: (123) 456-7890</div>
        </div>

        <div class="receipt-details">
            <table>
                <tr>
                    <td><strong>Receipt No:</strong></td>
                    <td class="receipt-no">{{ $receipt_no }}</td>
                    <td><strong>Date:</strong></td>
                    <td>{{ $purchase_date }}</td>
                </tr>
                <tr>
                    <td><strong>Customer:</strong></td>
                    <td colspan="3">{{ $receipt['user'] }}</td>
                </tr>
            </table>
        </div>

        <div class="payment-status payment-{{ strtolower($receipt['payment_status']) }}">
            Payment Status: {{ ucfirst($receipt['payment_status']) }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th class="amount">Unit Price</th>
                    <th class="amount">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $receipt['medicine']['name'] }}</td>
                    <td>{{ $receipt['medicine']['quantity'] }}</td>
                    <td class="amount">₱{{ number_format($receipt['medicine']['price'], 2) }}</td>
                    <td class="amount">₱{{ number_format($receipt['medicine']['subtotal'], 2) }}</td>
                </tr>
                <tr class="total">
                    <td colspan="3" class="amount">Total Amount:</td>
                    <td class="amount">₱{{ number_format($receipt['total_amount'], 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Thank you for choosing PureMed Pharmacy!</p>
            <p>This is an official receipt. Please keep this for your records.</p>
            <p>For questions or concerns, please contact our customer service.</p>
        </div>
    </div>
</body>
</html>
