<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    
    <!-- html2pdf.js for client-side PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
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
            background: #f5f5f5;
        }
        .invoice-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }
        .action-buttons {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .action-buttons button {
            padding: 12px 30px;
            margin: 0 10px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-download {
            background: #4F46E5;
            color: white;
        }
        .btn-download:hover {
            background: #4338CA;
        }
        .btn-print {
            background: #10B981;
            color: white;
        }
        .btn-print:hover {
            background: #059669;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
        .company-details {
            margin-top: 15px;
            color: #666;
            font-size: 13px;
            line-height: 1.8;
        }
        .bank-details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 13px;
        }
        .bank-details h3 {
            color: #4F46E5;
            font-size: 14px;
            margin-bottom: 8px;
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
        .invoice-details h3 {
            margin-bottom: 15px;
            color: #4F46E5;
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
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .action-buttons {
                display: none;
            }
            .invoice-container {
                box-shadow: none;
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-wrapper">
        <!-- Action Buttons (Hidden in PDF) -->
        <div class="action-buttons no-print">
            <button class="btn-download" onclick="downloadPDF()">
                <i class="fa fa-download"></i> Download PDF
            </button>
            <button class="btn-print" onclick="window.print()">
                <i class="fa fa-print"></i> Print
            </button>
        </div>

        <!-- Invoice Content -->
        <div class="invoice-container" id="invoice-content">
        <!-- Header -->
        <div class="header">
            <h1>INVOICE</h1>
            <p>Namate24 - Your Learning Platform</p>
             <div class="company-details">
                <strong>Namate24 Training Services OPC Pvt Ltd</strong><br>
                Building 154, 3rd Cross, Golahalli Main,<br>
                Electronic City Phase 1,<br>
                Bengaluru, Karnataka, India – 560100<br>
                Email: trainhireh@gmail.com
            </div>
        </div>
        <div class="bank-details">
        <h3>Company Registration Details</h3>
        GST No: 29AAICN7245B1ZE<br>
        CIN: U70200KA20230PC17076
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
    </div>

    <script>
        function downloadPDF() {
            const element = document.getElementById('invoice-content');
            const opt = {
                margin: 10,
                filename: 'Invoice-{{ $invoice->invoice_number }}.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            // Generate PDF on client side
            html2pdf().set(opt).from(element).save();
        }

        // Optional: Auto-download on load if URL parameter is present
        if (window.location.search.includes('download=true')) {
            setTimeout(downloadPDF, 500);
        }
    </script>
</body>
</html>
