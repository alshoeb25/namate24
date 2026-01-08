<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wallet Receipt</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f7f8fa; color: #1f2933; margin: 0; padding: 24px; }
        .card { max-width: 720px; margin: 0 auto; background: #fff; border-radius: 16px; padding: 24px; box-shadow: 0 12px 32px rgba(0,0,0,0.06); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .title { font-size: 24px; font-weight: 700; }
        .badge { padding: 6px 12px; border-radius: 999px; font-weight: 600; font-size: 12px; }
        .success { background: #ecfdf3; color: #15803d; }
        .pending { background: #fff7ed; color: #c2410c; }
        .failed  { background: #fef2f2; color: #b91c1c; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
        .section { margin-bottom: 20px; }
        .label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; margin-bottom: 4px; }
        .value { font-size: 16px; font-weight: 700; color: #111827; }
        .muted { color: #6b7280; font-size: 14px; }
        .totals { margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 12px; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div>
                <div class="muted">Namate24</div>
                <div class="title">Wallet Receipt</div>
            </div>
            @php
                $status = strtolower($order->status ?? '');
                $statusClass = $status === 'completed' ? 'success' : ($status === 'pending' ? 'pending' : 'failed');
                $statusLabel = $status === 'completed' ? 'Completed' : ($status === 'pending' ? 'Pending' : 'Failed');
            @endphp
            <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>

        <div class="section grid">
            <div>
                <div class="label">Order ID</div>
                <div class="value">{{ $order->razorpay_order_id ?? $order->id }}</div>
                @if($transaction?->id)
                    <div class="muted">Txn: {{ $transaction->id }}</div>
                @endif
            </div>
            <div>
                <div class="label">Payment ID</div>
                <div class="value">{{ $order->razorpay_payment_id ?? '—' }}</div>
            </div>
            <div>
                <div class="label">Date</div>
                <div class="value">{{ optional($order->paid_at ?? $order->created_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</div>
            </div>
            <div>
                <div class="label">Customer</div>
                <div class="value">{{ $user->name }}</div>
                <div class="muted">{{ $user->email }}</div>
            </div>
        </div>

        <div class="section grid">
            <div>
                <div class="label">Plan</div>
                <div class="value">{{ $order->package->name ?? 'Wallet Top-up' }}</div>
            </div>
            <div>
                <div class="label">Coins Added</div>
                <div class="value">{{ number_format($totalCoins) }} ({{ number_format($coins) }} base @if($bonus>0)+ {{ number_format($bonus) }} bonus @endif)</div>
            </div>
            <div>
                <div class="label">Amount</div>
                @php
                    $currency = $order->currency ?? 'INR';
                    $symbol = $currency === 'USD' ? '$' : '₹';
                @endphp
                <div class="value">{{ $symbol }}{{ number_format($order->amount, 2) }}</div>
            </div>
            <div>
                <div class="label">Status</div>
                <div class="value">{{ ucfirst($statusLabel) }}</div>
            </div>
        </div>

        <div class="section">
            <div class="label">Payment Details</div>
            <div class="grid">
                <div>
                    <div class="muted">Currency</div>
                    <div class="value">{{ $order->currency ?? 'INR' }}</div>
                </div>
                <div>
                    <div class="muted">Receipt</div>
                    <div class="value">{{ $order->receipt ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="totals">
                @php
                    $currency = $order->currency ?? 'INR';
                    $symbol = $currency === 'USD' ? '$' : '₹';
                    $pricing = $order->meta['pricing'] ?? null;
                    $isIndia = $pricing['is_india'] ?? ($currency === 'INR');
                    if ($currency === 'INR' && $isIndia && $pricing) {
                        $subtotal = (float) ($pricing['subtotal_inr'] ?? $order->amount);
                        $taxAmount = (float) ($pricing['tax_amount_inr'] ?? 0);
                    } else {
                        $subtotal = (float) $order->amount;
                        $taxAmount = 0.0;
                    }
                @endphp
                <div class="grid">
                    <div class="muted">Subtotal</div>
                    <div class="value">{{ $symbol }}{{ number_format($subtotal, 2) }}</div>
                    <div class="muted">Tax{{ ($currency === 'INR' && $isIndia) ? ' (GST)' : '' }}</div>
                    <div class="value">{{ $symbol }}{{ number_format($taxAmount, 2) }}</div>
                    <div class="muted">Total Paid</div>
                    <div class="value">{{ $symbol }}{{ number_format($order->amount, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="muted" style="margin-top: 12px;">Thank you for your purchase.</div>
    </div>
</body>
</html>
