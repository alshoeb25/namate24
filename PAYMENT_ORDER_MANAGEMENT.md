# Payment & Order Management System - Complete Guide

## Overview
Enhanced backend payment system with comprehensive order tracking, payment status management, Razorpay webhook integration, and redirect URLs.

## New Features Added

### 1. **Payment History Endpoint**
Advanced payment history with filtering, search, and statistics.

**Endpoint**: `GET /api/wallet/payment-history`

**Query Parameters**:
- `type` - Filter by transaction type (all, purchase, referral_bonus, booking, etc.)
- `status` - Filter by payment status (pending, completed, failed)
- `search` - Search by description, payment ID, or order ID
- `from_date` - Filter from date (YYYY-MM-DD)
- `to_date` - Filter to date (YYYY-MM-DD)
- `per_page` - Records per page (default: 20)

**Response**:
```json
{
  "transactions": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "type": "purchase",
        "amount": 100,
        "balance_after": 100,
        "description": "Purchased Starter Pack - 100 coins",
        "payment_id": "pay_xxxxx",
        "order_id": "order_xxxxx",
        "meta": {
          "package_id": 1,
          "status": "completed",
          "captured_at": "2025-12-19T10:30:00Z"
        },
        "created_at": "2025-12-19T10:30:00Z"
      }
    ],
    "total": 10,
    "per_page": 20
  },
  "stats": {
    "total_spent": 499,
    "total_earned": 75,
    "total_purchases": 3,
    "failed_payments": 1
  }
}
```

### 2. **Payment Order Status Tracking**
Get real-time status of any payment order.

**Endpoint**: `GET /api/wallet/order/{orderId}/status`

**Response**:
```json
{
  "order_id": "order_NZOlWUvX5PoMHM",
  "payment_id": "pay_NZOlWUvX5PoMHM",
  "status": "completed",
  "amount": 100,
  "description": "Purchased Starter Pack",
  "created_at": "2025-12-19T10:30:00Z",
  "updated_at": "2025-12-19T10:31:00Z",
  "meta": {
    "package_id": 1,
    "status": "completed",
    "payment_method": "card",
    "payment_email": "user@example.com"
  }
}
```

### 3. **Cancel Pending Payment**
Cancel a payment that hasn't been processed yet.

**Endpoint**: `POST /api/wallet/order/{orderId}/cancel`

**Response**:
```json
{
  "message": "Payment cancelled successfully",
  "order_id": "order_NZOlWUvX5PoMHM"
}
```

### 4. **Razorpay Webhook Handler**
Automatic payment status updates via Razorpay webhooks.

**Endpoint**: `POST /api/wallet/webhook` (Public - for Razorpay)

**Events Handled**:
- `payment.captured` - Payment successful, coins credited automatically
- `payment.failed` - Payment failed, transaction marked as failed
- `order.paid` - Order paid confirmation

**Webhook Configuration**:
1. Go to Razorpay Dashboard → Settings → Webhooks
2. Add webhook URL: `https://yourdomain.com/api/wallet/webhook`
3. Select events: `payment.captured`, `payment.failed`, `order.paid`
4. Copy webhook secret and add to `.env`:
   ```
   RAZORPAY_WEBHOOK_SECRET=your_webhook_secret_here
   ```

### 5. **Payment Callback URL**
Redirect users back to your app after payment.

**Endpoint**: `GET /api/wallet/payment-callback` (Public)

**Query Parameters** (from Razorpay):
- `razorpay_payment_id`
- `razorpay_order_id`
- `razorpay_signature`

**Behavior**:
- Finds transaction by order ID
- Redirects to appropriate wallet page:
  - Tutor: `/tutor/wallet?payment=processing`
  - Student: `/student/wallet?payment=processing`

### 6. **Enhanced Purchase Response**
Now includes redirect URLs and callback info.

**Endpoint**: `POST /api/wallet/purchase`

**Enhanced Response**:
```json
{
  "order": {
    "id": "order_NZOlWUvX5PoMHM",
    "amount": 9900,
    "currency": "INR",
    "receipt": "coin_1_1734597600"
  },
  "transaction_id": 123,
  "package": {
    "id": 1,
    "name": "Starter Pack",
    "coins": 100,
    "bonus_coins": 0,
    "price": 99
  },
  "user": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+919876543210"
  },
  "callback_url": "https://yourdomain.com/api/wallet/payment-callback",
  "redirect": {
    "success_url": "https://yourdomain.com/tutor/wallet?payment=success",
    "cancel_url": "https://yourdomain.com/tutor/wallet?payment=cancelled"
  }
}
```

## Payment Flow Diagram

```
1. User clicks "Buy Coins"
   ↓
2. Frontend calls POST /api/wallet/purchase
   ↓
3. Backend creates Razorpay order
   Backend creates transaction (status: pending)
   ↓
4. Frontend receives order details + callback URLs
   ↓
5. Frontend opens Razorpay checkout modal
   ↓
6. User enters payment details
   ↓
7. Payment success → Razorpay calls webhook
   ↓
8. Webhook handler (payment.captured):
   - Verifies signature
   - Credits coins to user
   - Updates transaction (status: completed)
   ↓
9. User redirected to success page
   Frontend shows success toast
```

## Transaction Status Flow

```
PENDING → User created order
   ↓
   ├─→ COMPLETED → Payment captured, coins credited
   ├─→ FAILED → Payment failed/declined
   └─→ CANCELLED → User cancelled payment
```

## Frontend Integration

### Using Enhanced Purchase API

```javascript
const purchasePackage = async (pkg) => {
  try {
    const { data } = await axios.post('/api/wallet/purchase', {
      package_id: pkg.id
    });

    const options = {
      key: import.meta.env.VITE_RAZORPAY_KEY,
      amount: data.order.amount,
      currency: 'INR',
      order_id: data.order.id,
      name: 'Namate24',
      description: `Purchase ${data.package.name}`,
      handler: async function (response) {
        // Option 1: Verify on client (existing method)
        await axios.post('/api/wallet/verify-payment', {
          transaction_id: data.transaction_id,
          razorpay_payment_id: response.razorpay_payment_id,
          razorpay_order_id: response.razorpay_order_id,
          razorpay_signature: response.razorpay_signature
        });
        
        showToast('Payment successful!', 'success');
        fetchWallet();
      },
      prefill: {
        name: data.user.name,
        email: data.user.email,
        contact: data.user.phone
      },
      // Use callback URL for redirect
      callback_url: data.callback_url,
      redirect: true, // Enable redirect after payment
      theme: {
        color: '#ec4899'
      },
      modal: {
        ondismiss: function() {
          showToast('Payment cancelled', 'error');
        }
      }
    };

    const rzp = new Razorpay(options);
    rzp.on('payment.failed', function (response) {
      showToast(`Payment failed: ${response.error.description}`, 'error');
    });
    rzp.open();
  } catch (error) {
    const errorMsg = error.response?.data?.message || 'Failed to initiate purchase';
    showToast(errorMsg, 'error');
  }
};
```

### Check Order Status

```javascript
const checkOrderStatus = async (orderId) => {
  try {
    const { data } = await axios.get(`/api/wallet/order/${orderId}/status`);
    
    if (data.status === 'completed') {
      showToast('Payment successful! Coins credited.', 'success');
    } else if (data.status === 'failed') {
      showToast('Payment failed. Please try again.', 'error');
    } else {
      showToast('Payment is being processed...', 'info');
    }
  } catch (error) {
    console.error('Failed to check order status:', error);
  }
};
```

### Cancel Pending Payment

```javascript
const cancelOrder = async (orderId) => {
  try {
    const { data } = await axios.post(`/api/wallet/order/${orderId}/cancel`);
    showToast('Payment cancelled successfully', 'success');
  } catch (error) {
    const errorMsg = error.response?.data?.error || 'Cannot cancel this payment';
    showToast(errorMsg, 'error');
  }
};
```

### Fetch Payment History with Filters

```javascript
const fetchPaymentHistory = async (filters = {}) => {
  try {
    const params = {
      type: filters.type || 'all',
      status: filters.status,
      search: filters.search,
      from_date: filters.fromDate,
      to_date: filters.toDate,
      per_page: 20
    };
    
    const { data } = await axios.get('/api/wallet/payment-history', { params });
    
    transactions.value = data.transactions.data;
    stats.value = data.stats;
    
    console.log('Total Spent:', data.stats.total_spent);
    console.log('Total Earned:', data.stats.total_earned);
  } catch (error) {
    console.error('Failed to fetch payment history:', error);
  }
};

// Usage
fetchPaymentHistory({ type: 'purchase', from_date: '2025-12-01' });
```

## Database Schema

### Transaction Meta JSON Structure

```json
{
  "package_id": 1,
  "package_name": "Starter Pack",
  "coins": 100,
  "bonus_coins": 0,
  "price": 99,
  "status": "completed",
  "captured_at": "2025-12-19T10:31:00Z",
  "payment_method": "card",
  "payment_email": "user@example.com",
  "error_code": null,
  "error_description": null,
  "failure_reason": null,
  "cancelled_at": null,
  "order_status": "paid",
  "paid_at": "2025-12-19T10:31:00Z"
}
```

## Environment Variables

### Required in `.env`

```env
# Razorpay Configuration
RAZORPAY_KEY=rzp_live_xxxxxxxxxxxx
RAZORPAY_SECRET=your_razorpay_secret
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret

# Frontend
VITE_RAZORPAY_KEY=rzp_live_xxxxxxxxxxxx

# App URL (for callbacks)
APP_URL=https://yourdomain.com
```

## API Routes Summary

### Protected Routes (Require Authentication)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/wallet` | Get wallet balance and recent transactions |
| GET | `/api/wallet/payment-history` | Get filtered payment history with stats |
| GET | `/api/wallet/packages` | Get available coin packages |
| POST | `/api/wallet/purchase` | Create Razorpay order for coin purchase |
| POST | `/api/wallet/verify-payment` | Verify payment signature and credit coins |
| GET | `/api/wallet/referral` | Get referral code and referred users |
| POST | `/api/wallet/apply-referral` | Apply referral code |
| GET | `/api/wallet/order/{orderId}/status` | Get payment order status |
| POST | `/api/wallet/order/{orderId}/cancel` | Cancel pending payment |

### Public Routes (No Authentication)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/wallet/webhook` | Razorpay webhook for payment updates |
| GET | `/api/wallet/payment-callback` | Payment redirect callback |

## Webhook Security

### Signature Verification

Razorpay webhooks are verified using HMAC SHA256:

```php
$webhookSecret = config('services.razorpay.webhook_secret');
$webhookSignature = $request->header('X-Razorpay-Signature');
$webhookBody = $request->getContent();

$expectedSignature = hash_hmac('sha256', $webhookBody, $webhookSecret);

if (!hash_equals($expectedSignature, $webhookSignature)) {
    return response()->json(['error' => 'Invalid signature'], 400);
}
```

## Error Handling

### Transaction Failure Handling

When payment verification fails, transaction is automatically updated:

```php
$transaction->update([
    'meta' => array_merge($meta, [
        'status' => 'failed',
        'failure_reason' => 'Invalid signature',
    ]),
]);
```

### Webhook Failure Logging

All webhook events are logged:

```php
\Log::info('Razorpay webhook received', $request->all());
\Log::error('Webhook processing failed: ' . $e->getMessage());
```

## Testing

### Test Payment Success Flow

1. Use Razorpay test mode credentials
2. Test card: `4111 1111 1111 1111`
3. CVV: Any 3 digits
4. Expiry: Any future date
5. Check webhook logs
6. Verify coins credited
7. Check transaction status

### Test Payment Failure Flow

1. Use test card: `4000 0000 0000 0002` (always fails)
2. Verify transaction marked as failed
3. Check error details in meta
4. Verify webhook received
5. User notified of failure

### Test Webhook Integration

```bash
# Use Razorpay Webhook Simulator
# Or use cURL to simulate webhook:

curl -X POST https://yourdomain.com/api/wallet/webhook \
  -H "Content-Type: application/json" \
  -H "X-Razorpay-Signature: generated_signature" \
  -d '{
    "event": "payment.captured",
    "payload": {
      "payment": {
        "entity": {
          "id": "pay_xxxxx",
          "order_id": "order_xxxxx",
          "amount": 9900,
          "method": "card",
          "email": "user@example.com"
        }
      }
    }
  }'
```

## Monitoring & Analytics

### Key Metrics to Track

1. **Payment Success Rate**
   ```sql
   SELECT 
     COUNT(CASE WHEN JSON_EXTRACT(meta, '$.status') = 'completed' THEN 1 END) * 100.0 / COUNT(*) as success_rate
   FROM coin_transactions
   WHERE type = 'purchase';
   ```

2. **Average Purchase Value**
   ```sql
   SELECT AVG(JSON_EXTRACT(meta, '$.price')) as avg_purchase
   FROM coin_transactions
   WHERE type = 'purchase' AND JSON_EXTRACT(meta, '$.status') = 'completed';
   ```

3. **Failed Payments Analysis**
   ```sql
   SELECT 
     JSON_EXTRACT(meta, '$.failure_reason') as reason,
     COUNT(*) as count
   FROM coin_transactions
   WHERE JSON_EXTRACT(meta, '$.status') = 'failed'
   GROUP BY reason;
   ```

4. **Revenue by Package**
   ```sql
   SELECT 
     JSON_EXTRACT(meta, '$.package_name') as package,
     COUNT(*) as purchases,
     SUM(JSON_EXTRACT(meta, '$.price')) as revenue
   FROM coin_transactions
   WHERE type = 'purchase' AND JSON_EXTRACT(meta, '$.status') = 'completed'
   GROUP BY package;
   ```

## Production Checklist

- [ ] Replace test Razorpay keys with production keys
- [ ] Configure webhook secret in `.env`
- [ ] Set up webhook URL in Razorpay Dashboard
- [ ] Test webhook delivery with Razorpay simulator
- [ ] Enable HTTPS for webhook endpoint
- [ ] Set up monitoring for failed webhooks
- [ ] Configure email notifications for failed payments
- [ ] Set up automated payment reconciliation
- [ ] Test redirect URLs on production domain
- [ ] Review and optimize database indexes
- [ ] Set up backup for transaction data
- [ ] Configure rate limiting for webhook endpoint
- [ ] Test payment flow end-to-end on production

## Troubleshooting

### Issue: Webhook not received

**Solutions**:
1. Check Razorpay Dashboard → Webhooks → Event Logs
2. Verify webhook URL is accessible (use webhook.site for testing)
3. Check firewall/CORS settings
4. Verify webhook secret is correct

### Issue: Payment successful but coins not credited

**Solutions**:
1. Check webhook logs: `storage/logs/laravel.log`
2. Verify transaction status in database
3. Check if webhook signature validation passed
4. Manually trigger webhook from Razorpay Dashboard

### Issue: Signature verification failing

**Solutions**:
1. Verify `RAZORPAY_SECRET` in `.env`
2. Check signature calculation logic
3. Ensure payload is not modified before verification
4. Use `$request->getContent()` instead of `$request->all()`

---

## Support & Resources

- **Razorpay Documentation**: https://razorpay.com/docs/
- **Webhook Guide**: https://razorpay.com/docs/webhooks/
- **Test Cards**: https://razorpay.com/docs/payments/payments/test-card-details/
- **API Reference**: https://razorpay.com/docs/api/

**Last Updated**: December 19, 2025
