<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border: 1px solid #ddd;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 20px;
        }
        .header h1 {
            color: #4F46E5;
            font-size: 32px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .invoice-info div {
            flex: 1;
        }
        .invoice-info h3 {
            color: #4F46E5;
            font-size: 16px;
            margin-bottom: 10px;
        }
        .invoice-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        .invoice-details {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-details th {
            text-align: left;
            padding: 12px;
            background: #4F46E5;
            color: white;
            font-weight: 600;
        }
        .invoice-details td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        .total-section {
            text-align: right;
            margin-top: 20px;
        }
        .total-section table {
            margin-left: auto;
            width: 300px;
        }
        .total-section td {
            padding: 8px;
        }
        .total-section .total-row {
            font-weight: bold;
            font-size: 18px;
            background: #4F46E5;
            color: white;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 12px;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background: #10B981;
            color: white;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <h1>INVOICE</h1>
            <p>Namate24 - Your Learning Platform</p>
        </div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div>
                <h3>Invoice To:</h3>
                <p><strong>{{ $user->name }}</strong></p>
                <p>{{ $user->email }}</p>
                @if($user->phone)
                <p>{{ $user->phone }}</p>
                @endif
            </div>
            <div style="text-align: right;">
                <h3>Invoice Details:</h3>
                <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Date:</strong> {{ $invoice->issued_at->format('d M, Y') }}</p>
                <p><strong>Status:</strong> <span class="badge">PAID</span></p>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="invoice-details">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: center;">Coins</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>{{ $transaction->meta['package_name'] ?? 'Coin Purchase' }}</strong>
                            <br>
                            <small>Base Coins: {{ $invoice->coins }}</small>
                            @if($invoice->bonus_coins > 0)
                            <br><small>Bonus Coins: {{ $invoice->bonus_coins }}</small>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            {{ $invoice->coins + $invoice->bonus_coins }}
                        </td>
                        <td style="text-align: right;">
                            @php
                                $currency = $invoice->currency ?? 'INR';
                                $symbol = $currency === 'USD' ? '$' : '₹';
                            @endphp
                            {{ $symbol }}{{ number_format($invoice->amount, 2) }} {{ $currency }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Total Section -->
            <div class="total-section">
                <table>
                    @php
                        $currency = $invoice->currency ?? 'INR';
                        $symbol = $currency === 'USD' ? '$' : '₹';
                        $pricing = $order->meta['pricing'] ?? null;
                        $isIndia = $pricing['is_india'] ?? ($currency === 'INR');
                        
                        // Get subtotal and tax from pricing meta or calculate
                        if ($pricing) {
                            $subtotal = (float) ($pricing['subtotal'] ?? $invoice->amount);
                            $taxAmount = (float) ($pricing['tax_amount'] ?? 0);
                            $gstRate = $pricing['gst_rate'] ?? 0;
                        } else {
                            // Fallback: assume no tax for non-India
                            $subtotal = (float) $invoice->amount;
                            $taxAmount = 0.0;
                            $gstRate = 0;
                        }
                    @endphp
                    <tr>
                        <td>Subtotal:</td>
                        <td style="text-align: right;">{{ $symbol }}{{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @if($taxAmount > 0)
                    <tr>
                        <td>Tax{{ ($currency === 'INR' && $isIndia) ? ' (GST ' . ($gstRate * 100) . '%)' : '' }}:</td>
                        <td style="text-align: right;">{{ $symbol }}{{ number_format($taxAmount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td>Total ({{ $currency }}):</td>
                        <td style="text-align: right;">{{ $symbol }}{{ number_format($invoice->amount, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="invoice-details">
            <h3 style="margin-bottom: 15px; color: #4F46E5;">Payment Information</h3>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 50%; padding: 8px;"><strong>Payment Method:</strong></td>
                    <td style="padding: 8px;">Razorpay</td>
                </tr>
                <tr>
                    <td style="padding: 8px;"><strong>Order ID:</strong></td>
                    <td style="padding: 8px;">{{ $order->razorpay_order_id }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px;"><strong>Payment ID:</strong></td>
                    <td style="padding: 8px;">{{ $order->razorpay_payment_id }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px;"><strong>Transaction Date:</strong></td>
                    <td style="padding: 8px;">{{ $order->paid_at->format('d M, Y h:i A') }}</td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your purchase!</strong></p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
            <p>For any queries, please contact support@namate24.com</p>
        </div>
    </div>
</body>
</html>
