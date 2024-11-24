<!DOCTYPE html>
<html>
<head>
    <title>Purchase Receipt - {{ $purchase['transaction_number'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            margin: 20px;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
        }
        .company-details, .footer {
            font-size: 10px;
            color: #666;
        }
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
            margin: 10px 0;
            text-transform: uppercase;
        }
        .details {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8fafc;
            border-radius: 4px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .table th, .table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #2563eb;
            color: white;
            font-weight: 600;
        }
        .total {
            text-align: right;
            margin-top: 15px;
            padding: 10px;
            background-color: #f8fafc;
            border-radius: 4px;
        }
        .total-amount {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
        }
        .transaction-number {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
        }
        .pickup-status {
            margin: 10px 0;
            padding: 8px;
            background-color: #f0fdf4;
            border: 1px solid #86efac;
            border-radius: 4px;
            color: #166534;
            font-size: 11px;
        }
        .signature-section {
            margin-top: 20px;
            text-align: center;
        }
        .signature-line {
            width: 150px;
            border-top: 2px solid #333;
            margin: 5px auto;
        }
        .signature-name {
            font-weight: bold;
            margin-top: 5px;
            font-size: 12px;
        }
        @page {
            margin: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">PureMed Pharmacy</div>
        <div class="company-details">
            Navarro St. Brgy. Taft, Surigao City 8400<br>
            Phone: (123) 456-7890 | Email: info@puremedpharmacy.com
        </div>
        <div class="receipt-title">Official Receipt</div>
        <div class="transaction-number">Transaction #: {{ $purchase['transaction_number'] }}</div>
    </div>

    <div class="details">
        <p><strong>Date:</strong> {{ $purchase['date'] }}</p>
        <p><strong>Customer:</strong> {{ $purchase['customer_name'] }}</p>
        <p><strong>Status:</strong> {{ ucfirst($purchase['status']) }}</p>
        <p><strong>Payment Status:</strong> {{ ucfirst($purchase['payment_status']) }}</p>
        @if($purchase['verification_date'] !== 'N/A')
        <p><strong>Verification Date:</strong> {{ $purchase['verification_date'] }}</p>
        @endif
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Medicine</th>
                <th>Dosage</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase['items'] as $item)
            <tr>
                <td>{{ $item['medicine_name'] }}</td>
                <td>{{ $item['dosage'] }}</td>
                <td>{{ $item['quantity'] }}</td>
                <td>PHP{{ $item['unit_price'] }}</td>
                <td>PHP{{ $item['total_amount'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p class="total-amount">Total: PHP{{ $purchase['total_amount'] }}</p>
    </div>

    <div class="pickup-status">
        <strong>Pickup Status:</strong> 
        @if($purchase['status'] === 'completed')
            Picked up and verified
        @elseif($purchase['status'] === 'ready_for_pickup')
            Ready for pickup
        @else
            {{ ucfirst($purchase['status']) }}
        @endif
    </div>

    <div class="signature-section">
        <div class="signature-line"></div>
        <div class="signature-name">PureMed Pharmacy</div>
    </div>

    <div class="footer">
        Thank you for choosing PureMed Pharmacy!<br>
        For inquiries, call (123) 456-7890 or email info@puremedpharmacy.com
    </div>
</body>
</html>
