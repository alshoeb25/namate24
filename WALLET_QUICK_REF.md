# Wallet System - Quick Reference Guide

## 🚀 Quick Start

### For Users

#### Buy Coins
1. Navigate to **Wallet** dropdown in menu
2. Click **My Wallet**
3. Select a coin package
4. Complete payment via Razorpay
5. Coins credited instantly

#### View Transaction History
1. **Wallet** dropdown → **Payment History**
2. Use filters to view specific transaction types
3. Search by transaction ID
4. View stats: total spent, coins earned, success/failed counts

#### Refer Friends
1. **Wallet** dropdown → **Refer Friends**
2. Copy your unique referral code
3. Share with friends via social media
4. Earn **50 coins** for each successful referral
5. Your friend gets **25 welcome coins**

---

## 🔧 Developer Quick Reference

### API Endpoints Cheat Sheet

```javascript
// Get Wallet Balance & Transactions
GET /api/wallet
Response: { wallet: {}, transactions: [] }

// Get Coin Packages
GET /api/wallet/packages
Response: [{ id, name, coins, bonus_coins, price, ... }]

// Purchase Coins (Create Razorpay Order)
POST /api/wallet/purchase
Body: { package_id: 1 }
Response: { order: { id, amount }, transaction_id, user: {} }

// Verify Payment
POST /api/wallet/verify-payment
Body: { 
  transaction_id, 
  razorpay_payment_id, 
  razorpay_order_id, 
  razorpay_signature 
}
Response: { success: true, transaction: {}, balance: 100 }

// Get Referral Info
GET /api/wallet/referral
Response: { stats: {}, referrals: [] }

// Apply Referral Code
POST /api/wallet/apply-referral
Body: { referral_code: "ABC123" }
Response: { success: true, coins_awarded: 25 }
```

### Frontend Routes

```
Tutor Routes:
/tutor/wallet                          → TutorWallet.vue
/tutor/wallet/payment-history          → PaymentHistory.vue
/tutor/wallet/referrals                → ReferralPage.vue

Student Routes:
/student/wallet                        → StudentWallet.vue
/student/wallet/payment-history        → PaymentHistory.vue
/student/wallet/referrals              → ReferralPage.vue
```

### Key Components

| Component | Purpose | Theme Color |
|-----------|---------|-------------|
| TutorWallet.vue | Tutor wallet page | Pink |
| StudentWallet.vue | Student wallet page | Blue |
| PaymentHistory.vue | Transaction history | Gray |
| ReferralPage.vue | Referral program | Purple |

### Toast Notifications

```javascript
// Success Toast
showToast('Operation successful!', 'success')

// Error Toast
showToast('Something went wrong', 'error')
```

### Transaction Types Reference

```javascript
const transactionTypes = {
  purchase: 'Coin Purchase',
  referral_bonus: 'Referral Reward (Referrer)',
  referral_reward: 'Welcome Bonus (Referred)',
  booking: 'Booking Payment',
  refund: 'Booking Refund',
  admin_credit: 'Admin Credit',
  admin_debit: 'Admin Debit'
}
```

---

## 📦 Database Quick Reference

### Query Examples

```php
// Get user balance
$balance = User::find($userId)->coins;

// Get user transactions
$transactions = CoinTransaction::where('user_id', $userId)
    ->orderBy('created_at', 'desc')
    ->get();

// Get user referrals
$referrals = Referral::where('referrer_id', $userId)
    ->with('referred')
    ->get();

// Get active packages
$packages = CoinPackage::where('is_active', true)
    ->orderBy('price')
    ->get();

// Create transaction
CoinTransaction::create([
    'user_id' => $userId,
    'type' => 'purchase',
    'amount' => 100,
    'balance_after' => $user->coins,
    'description' => 'Purchased 100 coins',
    'payment_id' => $paymentId,
    'order_id' => $orderId,
    'status' => 'completed'
]);

// Award referral bonus
$referrer = User::find($referrerId);
$referrer->increment('coins', 50);

$referred = User::find($referredId);
$referred->increment('coins', 25);
```

---

## 🎨 UI Component Snippets

### Coin Balance Display

```vue
<div class="text-5xl font-bold flex items-center gap-2">
  <i class="fas fa-coins text-yellow-300"></i>{{ balance }}
</div>
```

### Package Card

```vue
<div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
  <div class="text-center">
    <i class="fas fa-coins text-4xl text-yellow-500 mb-4"></i>
    <h3 class="text-2xl font-bold">{{ package.coins }} Coins</h3>
    <p class="text-gray-500">{{ package.name }}</p>
    <p class="text-3xl font-bold text-pink-600 mt-4">₹{{ package.price }}</p>
    <button @click="purchasePackage(package)" 
            class="mt-4 w-full bg-pink-500 text-white py-3 rounded-lg">
      Buy Now
    </button>
  </div>
</div>
```

### Transaction Row

```vue
<tr class="border-b hover:bg-gray-50">
  <td class="py-4 px-4">
    <div class="flex items-center gap-3">
      <div :class="getIconClass(transaction.type)" class="w-10 h-10 rounded-full flex items-center justify-center">
        <i :class="getIcon(transaction.type)"></i>
      </div>
      <div>
        <p class="font-medium">{{ transaction.description }}</p>
        <p class="text-sm text-gray-500">{{ formatDate(transaction.created_at) }}</p>
      </div>
    </div>
  </td>
  <td class="py-4 px-4 text-right">
    <span :class="transaction.amount > 0 ? 'text-green-600' : 'text-red-600'" class="font-bold">
      {{ transaction.amount > 0 ? '+' : '' }}{{ transaction.amount }}
    </span>
  </td>
</tr>
```

---

## 🔐 Security Checklist

### Before Production
- [ ] Replace Razorpay test keys with production keys
- [ ] Verify webhook signature validation
- [ ] Enable HTTPS
- [ ] Set up proper CORS headers
- [ ] Configure rate limiting
- [ ] Add request validation
- [ ] Implement payment reconciliation
- [ ] Set up monitoring and alerts
- [ ] Review and test error handling
- [ ] Backup database before deployment

---

## 🐛 Quick Debugging

### Check Wallet Balance
```bash
php artisan tinker
>>> User::find(1)->coins
```

### View Recent Transactions
```bash
php artisan tinker
>>> CoinTransaction::latest()->take(10)->get()
```

### Check Razorpay Configuration
```bash
php artisan tinker
>>> config('services.razorpay')
```

### Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Run Migrations
```bash
php artisan migrate
php artisan db:seed --class=CoinPackageSeeder
```

---

## 📱 Test Cards (Razorpay)

### Success
- Card: `4111 1111 1111 1111`
- CVV: Any 3 digits
- Expiry: Any future date

### Failed
- Card: `4000 0000 0000 0002`
- CVV: Any 3 digits
- Expiry: Any future date

### More: https://razorpay.com/docs/payments/payments/test-card-details/

---

## 💡 Pro Tips

1. **Toast Duration**: Default 3 seconds, adjust in `setTimeout` if needed
2. **Dropdown Menu**: Uses CSS hover for desktop, `<details>` for mobile
3. **Shared Wallet**: Coins stored in `users.coins` column for both roles
4. **Transaction History**: Paginated by default (15 per page)
5. **Referral Code**: Auto-generated on user creation (8 characters)
6. **Bonus Coins**: Automatically calculated in package display
7. **Payment Status**: Check `coin_transactions.status` for pending payments
8. **Error Messages**: Always return user-friendly messages from API

---

## 🎯 Common Tasks

### Add New Coin Package
```php
CoinPackage::create([
    'name' => 'Mega Pack',
    'coins' => 10000,
    'bonus_coins' => 3000,
    'price' => 9999,
    'description' => 'Best value!',
    'is_active' => true,
    'popular' => true
]);
```

### Credit Coins to User (Admin)
```php
$user = User::find($userId);
$amount = 100;

$user->increment('coins', $amount);

CoinTransaction::create([
    'user_id' => $user->id,
    'type' => 'admin_credit',
    'amount' => $amount,
    'balance_after' => $user->coins,
    'description' => 'Admin credited coins',
    'status' => 'completed'
]);
```

### Deduct Coins for Booking
```php
$user = User::find($userId);
$amount = 50;

if ($user->coins >= $amount) {
    $user->decrement('coins', $amount);
    
    CoinTransaction::create([
        'user_id' => $user->id,
        'type' => 'booking',
        'amount' => -$amount,
        'balance_after' => $user->coins,
        'description' => 'Booking payment',
        'status' => 'completed',
        'meta' => json_encode(['booking_id' => $bookingId])
    ]);
}
```

---

## 📊 Monitoring Queries

### Total Coins in System
```sql
SELECT SUM(coins) as total_coins FROM users;
```

### Revenue by Package
```sql
SELECT cp.name, COUNT(*) as purchases, SUM(cp.price) as revenue
FROM coin_transactions ct
JOIN coin_packages cp ON JSON_EXTRACT(ct.meta, '$.package_id') = cp.id
WHERE ct.type = 'purchase' AND ct.status = 'completed'
GROUP BY cp.name;
```

### Top Referrers
```sql
SELECT u.name, COUNT(*) as referrals, SUM(r.referrer_coins) as coins_earned
FROM referrals r
JOIN users u ON r.referrer_id = u.id
WHERE r.status = 'completed'
GROUP BY u.id
ORDER BY referrals DESC
LIMIT 10;
```

### Failed Payments
```sql
SELECT COUNT(*) as failed_payments, SUM(amount) as lost_revenue
FROM coin_transactions
WHERE type = 'purchase' AND status = 'failed';
```

---

**Need Help?** Check WALLET_SYSTEM_COMPLETE.md for full documentation.
