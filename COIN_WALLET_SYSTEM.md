# Coin Wallet System - Complete Guide

## Overview
Complete coin-based wallet system with:
- **Coin Balance Management** - Track user coins
- **Purchase Coins** - Buy coin packages via Razorpay
- **Referral System** - Earn coins by inviting friends
- **Transaction History** - View all coin movements
- **Multiple Transaction Types** - Purchase, referral rewards, bookings, refunds, admin actions

## Database Structure

### Tables Created
1. **users** (modified)
   - `coins` - Integer, default 0
   - `referral_code` - Unique 8-character code
   - `referred_by` - Foreign key to users table

2. **coin_transactions**
   - Tracks all coin movements
   - Types: purchase, referral_bonus, referral_reward, booking, refund, admin_credit, admin_debit
   - Stores Razorpay payment_id and order_id

3. **referrals**
   - Links referrer and referred users
   - Tracks coins given to both parties

4. **coin_packages**
   - Purchasable coin bundles
   - Includes base coins + bonus coins

## API Endpoints

All endpoints require authentication (`Authorization: Bearer {token}`)

### 1. Get Wallet Information
```
GET /api/wallet
```

**Response:**
```json
{
  "balance": 150,
  "referral_code": "ABC12XYZ",
  "referral_stats": {
    "total_referrals": 3,
    "coins_earned": 150
  },
  "transactions": {
    "data": [
      {
        "id": 1,
        "type": "purchase",
        "amount": 100,
        "balance_after": 150,
        "description": "Purchased Popular Pack - 500 coins + 50 bonus",
        "created_at": "2025-12-19T10:30:00Z"
      }
    ],
    "pagination": {...}
  }
}
```

### 2. Get Coin Packages
```
GET /api/wallet/packages
```

**Response:**
```json
[
  {
    "id": 1,
    "name": "Starter Pack",
    "coins": 100,
    "price": "99.00",
    "bonus_coins": 0,
    "total_coins": 100,
    "description": "Perfect for trying out the platform",
    "is_popular": false
  },
  {
    "id": 3,
    "name": "Popular Pack",
    "coins": 500,
    "price": "499.00",
    "bonus_coins": 50,
    "total_coins": 550,
    "description": "Most popular! Get 50 bonus coins",
    "is_popular": true
  }
]
```

### 3. Purchase Coins (Create Razorpay Order)
```
POST /api/wallet/purchase
Content-Type: application/json

{
  "package_id": 3
}
```

**Response:**
```json
{
  "order": {
    "id": "order_MgkjqwertyuioplkjhgfdsQ",
    "amount": 49900,
    "currency": "INR",
    "receipt": "coin_123_1734612345"
  },
  "transaction_id": 45,
  "package": {
    "id": 3,
    "name": "Popular Pack",
    "coins": 500,
    "bonus_coins": 50,
    "price": "499.00"
  }
}
```

### 4. Verify Payment & Credit Coins
```
POST /api/wallet/verify-payment
Content-Type: application/json

{
  "transaction_id": 45,
  "razorpay_payment_id": "pay_MgkjwertyuioplkjhgfdsQ",
  "razorpay_order_id": "order_MgkjqwertyuioplkjhgfdsQ",
  "razorpay_signature": "9e2e8f7a6b5c4d3e2f1a0b9c8d7e6f5a4b3c2d1e0f9a8b7c6d5e4f3a2b1c0d9e"
}
```

**Response:**
```json
{
  "message": "Payment successful! Coins credited.",
  "coins_added": 550,
  "balance": 700
}
```

### 5. Get Referral Information
```
GET /api/wallet/referral
```

**Response:**
```json
{
  "stats": {
    "total_referrals": 3,
    "total_coins_earned": 150,
    "referral_code": "ABC12XYZ",
    "referral_link": "https://namate24.com/register?ref=ABC12XYZ"
  },
  "referrals": [
    {
      "id": 1,
      "referrer_coins": 50,
      "referred_coins": 25,
      "reward_given_at": "2025-12-19T10:00:00Z",
      "referred": {
        "id": 25,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-12-19T10:00:00Z"
      }
    }
  ]
}
```

### 6. Apply Referral Code
```
POST /api/wallet/apply-referral
Content-Type: application/json

{
  "referral_code": "ABC12XYZ"
}
```

**Response:**
```json
{
  "message": "Referral applied! You earned 25 coins.",
  "coins_earned": 25,
  "balance": 25
}
```

## Coin Packages (Seeded)

| Package | Base Coins | Bonus Coins | Total | Price (INR) |
|---------|------------|-------------|-------|-------------|
| Starter Pack | 100 | 0 | 100 | ₹99 |
| Basic Pack | 250 | 10 | 260 | ₹249 |
| Popular Pack ⭐ | 500 | 50 | 550 | ₹499 |
| Value Pack | 1000 | 150 | 1150 | ₹999 |
| Premium Pack | 2500 | 500 | 3000 | ₹2499 |
| Ultimate Pack | 5000 | 1500 | 6500 | ₹4999 |

## Referral System

### How it Works
1. Each user gets a unique referral code automatically
2. Share referral link with friends
3. When someone registers using your code:
   - **Referrer gets:** 50 coins
   - **New user gets:** 25 coins
4. Both transactions recorded in `coin_transactions` table

### Restrictions
- Can only use referral code once
- Cannot use your own referral code
- Referral rewards given immediately upon registration

## Razorpay Integration

### Setup Steps
1. Add Razorpay credentials to `.env`:
```env
RAZORPAY_KEY=rzp_test_xxxxxxxxxxxx
RAZORPAY_SECRET=xxxxxxxxxxxxxxxxxxxxxxxx
```

2. Get test credentials from: https://dashboard.razorpay.com/app/keys

### Frontend Integration Example
```javascript
// 1. Create order
const { data } = await axios.post('/api/wallet/purchase', {
  package_id: 3
});

// 2. Initialize Razorpay
const options = {
  key: 'rzp_test_xxxxxxxxxxxx',
  amount: data.order.amount,
  currency: 'INR',
  order_id: data.order.id,
  name: 'Namate24',
  description: 'Coin Purchase',
  handler: async function (response) {
    // 3. Verify payment
    await axios.post('/api/wallet/verify-payment', {
      transaction_id: data.transaction_id,
      razorpay_payment_id: response.razorpay_payment_id,
      razorpay_order_id: response.razorpay_order_id,
      razorpay_signature: response.razorpay_signature
    });
    
    // Coins credited! Reload wallet
    await fetchWallet();
  }
};

const rzp = new Razorpay(options);
rzp.open();
```

## Transaction Types

| Type | Description | Amount Sign |
|------|-------------|-------------|
| `purchase` | Coins bought via Razorpay | Positive |
| `referral_bonus` | Coins received as referred user | Positive |
| `referral_reward` | Coins earned from referring someone | Positive |
| `booking` | Coins spent on tutor booking | Negative |
| `refund` | Coins refunded from cancelled booking | Positive |
| `admin_credit` | Admin manually added coins | Positive |
| `admin_debit` | Admin manually removed coins | Negative |

## Models & Relationships

### User Model
```php
// Relationships added
public function coinTransactions(): HasMany
public function referrals(): HasMany
public function referredBy(): BelongsTo
```

### CoinTransaction Model
```php
protected $fillable = [
    'user_id', 'type', 'amount', 'balance_after',
    'description', 'payment_id', 'order_id', 'meta'
];
```

### Referral Model
```php
public function referrer(): BelongsTo // User who referred
public function referred(): BelongsTo // User who got referred
```

### CoinPackage Model
```php
public function getTotalCoinsAttribute(): int // coins + bonus_coins
public function scopeActive(Builder $query): Builder
public function scopePopular(Builder $query): Builder
```

## Security Features

1. **Payment Signature Verification**
   - HMAC SHA256 validation of Razorpay signatures
   - Prevents payment tampering

2. **Idempotency**
   - Duplicate payment verification prevented
   - Safe to retry failed requests

3. **Authorization Checks**
   - Transaction ownership validated
   - Referral code usage restrictions

4. **Database Transactions**
   - Atomic operations for coin credits
   - Rollback on errors

## Testing Checklist

### Wallet & Transactions
- ✅ Get wallet balance and transaction history
- ✅ Paginated transaction list

### Coin Purchase
- ✅ Fetch coin packages
- ✅ Create Razorpay order
- ✅ Verify payment signature
- ✅ Credit coins after successful payment
- ✅ Handle duplicate payment verification
- ✅ Bonus coins included in total

### Referral System
- ✅ Generate unique referral code
- ✅ Share referral link
- ✅ Apply referral code during registration
- ✅ Credit coins to both users
- ✅ Track referral stats
- ✅ Prevent self-referral
- ✅ Prevent duplicate referral usage

## Future Enhancements

1. **Coin Expiry** - Add expiry dates for purchased coins
2. **Coin Gifting** - Allow users to transfer coins to others
3. **Coin Offers** - Special promotional offers and discounts
4. **Loyalty Program** - Bonus coins for active users
5. **Coin Leaderboard** - Show top coin earners
6. **Bulk Purchases** - Corporate/institutional packages
7. **Auto-debit Bookings** - Automatic coin deduction for bookings

## Support & Troubleshooting

### Common Issues

**Issue:** Payment verified but coins not credited
- Check `coin_transactions` table for pending status
- Verify `balance_after` field updated
- Check Laravel logs for errors

**Issue:** Referral code not working
- Ensure code exists in `users.referral_code`
- Check if already used (`users.referred_by`)
- Verify user not using own code

**Issue:** Razorpay signature mismatch
- Verify Razorpay secret key correct
- Check order_id matches transaction
- Ensure payload format correct

## Environment Variables Required

```env
# Razorpay Configuration
RAZORPAY_KEY=rzp_test_xxxxxxxxxxxx
RAZORPAY_SECRET=xxxxxxxxxxxxxxxxxxxxxxxx

# Frontend URL (for referral links)
APP_FRONTEND_URL=https://namate24.com
```

## Database Queries for Admin

```sql
-- Total coins purchased
SELECT SUM(amount) FROM coin_transactions WHERE type = 'purchase';

-- Top referrers
SELECT u.name, COUNT(r.id) as total_referrals, SUM(r.referrer_coins) as coins_earned
FROM users u
JOIN referrals r ON u.id = r.referrer_id
GROUP BY u.id
ORDER BY total_referrals DESC
LIMIT 10;

-- Coin package popularity
SELECT cp.name, COUNT(ct.id) as purchases
FROM coin_packages cp
JOIN coin_transactions ct ON JSON_EXTRACT(ct.meta, '$.package_id') = cp.id
WHERE ct.type = 'purchase' AND ct.payment_id IS NOT NULL
GROUP BY cp.id
ORDER BY purchases DESC;
```

---

**System Status:** ✅ Fully Implemented & Tested
**Last Updated:** December 19, 2025
**Version:** 1.0.0
