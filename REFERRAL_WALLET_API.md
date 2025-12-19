# Referral & Wallet System API Documentation

## Overview
Complete backend implementation for the referral and coin wallet system. Users earn 50 coins per successful referral, and new users get 25 welcome coins.

---

## Referral System Features

### 🎁 Rewards Structure
- **Referrer**: 50 coins per successful referral
- **New User**: 25 welcome coins
- **Unlimited Referrals**: No limit on how many friends you can refer

### 🔑 Key Features
- Auto-generated unique referral codes for all users
- Referral code validation during registration
- Prevents self-referral and duplicate usage
- Tracks all referrals with complete history
- Real-time coin credits for both parties

---

## API Endpoints

### 1. Register with Referral Code

**Endpoint:** `POST /api/register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+919876543210",
  "password": "password123",
  "role": "student",
  "referral_code": "ABC12345"
}
```

**Response (Success):**
```json
{
  "message": "Registration successful! You earned 25 coins from the referral!",
  "user": {
    "id": 10,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+919876543210",
    "role": "student",
    "coins": 25,
    "referral_code": "XYZ98765",
    "referred_by": 5
  },
  "roles": ["student"],
  "email_sent": true,
  "referral_applied": true,
  "referral_reward": {
    "coins": 25,
    "referrer_name": "Jane Smith"
  },
  "coins": 25
}
```

**Notes:**
- `referral_code` field is optional during registration
- If valid referral code provided, coins are credited immediately
- New user gets their own unique referral code automatically
- Referral bonus appears in transaction history

---

### 2. Validate Referral Code

**Endpoint:** `POST /api/validate-referral-code`

**Purpose:** Check if a referral code is valid before registration

**Request Body:**
```json
{
  "referral_code": "ABC12345"
}
```

**Response (Valid):**
```json
{
  "valid": true,
  "message": "Valid referral code",
  "referrer": {
    "name": "Jane Smith",
    "referral_code": "ABC12345"
  },
  "reward": {
    "referrer_coins": 50,
    "referred_coins": 25
  }
}
```

**Response (Invalid):**
```json
{
  "valid": false,
  "message": "Invalid referral code"
}
```

---

### 3. Get Wallet Balance & Transactions

**Endpoint:** `GET /api/wallet`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "balance": 175,
  "referral_code": "XYZ98765",
  "referral_stats": {
    "total_referrals": 3,
    "coins_earned": 150
  },
  "transactions": {
    "data": [
      {
        "id": 1,
        "type": "referral_bonus",
        "amount": 25,
        "balance_after": 175,
        "description": "Welcome bonus for using Jane Smith's referral code",
        "created_at": "2025-12-19T10:30:00.000000Z"
      }
    ],
    "current_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

---

### 4. Get Referral Information

**Endpoint:** `GET /api/wallet/referral`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "stats": {
    "total_referrals": 5,
    "total_coins_earned": 250,
    "referral_code": "ABC12345",
    "referral_link": "https://namate24.com/register?ref=ABC12345"
  },
  "referrals": [
    {
      "id": 1,
      "referrer_id": 5,
      "referred_id": 10,
      "referrer_coins": 50,
      "referred_coins": 25,
      "reward_given": true,
      "reward_given_at": "2025-12-19T10:30:00.000000Z",
      "created_at": "2025-12-19T10:30:00.000000Z",
      "referred": {
        "id": 10,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-12-19T10:30:00.000000Z"
      }
    }
  ]
}
```

---

### 5. Apply Referral Code (Post-Registration)

**Endpoint:** `POST /api/wallet/apply-referral`

**Headers:** `Authorization: Bearer {token}`

**Purpose:** Apply referral code if not provided during registration

**Request Body:**
```json
{
  "referral_code": "ABC12345"
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

**Error Responses:**
```json
// Already used a referral code
{
  "message": "You have already used a referral code."
}

// Invalid code
{
  "message": "Invalid referral code"
}

// Self-referral attempt
{
  "message": "You cannot use your own referral code"
}
```

---

### 6. Get Coin Packages

**Endpoint:** `GET /api/wallet/packages`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
[
  {
    "id": 1,
    "name": "Starter Pack",
    "coins": 100,
    "bonus_coins": 10,
    "price": 99,
    "is_active": true,
    "sort_order": 1
  },
  {
    "id": 2,
    "name": "Pro Pack",
    "coins": 500,
    "bonus_coins": 100,
    "price": 499,
    "is_active": true,
    "sort_order": 2
  }
]
```

---

### 7. Purchase Coins (Create Razorpay Order)

**Endpoint:** `POST /api/wallet/purchase`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "package_id": 1
}
```

**Response:**
```json
{
  "order": {
    "id": "order_MN4xYZ123456",
    "amount": 9900,
    "currency": "INR",
    "receipt": "coin_10_1734601200"
  },
  "transaction_id": 25,
  "package": {
    "id": 1,
    "name": "Starter Pack",
    "coins": 100,
    "bonus_coins": 10,
    "price": 99
  },
  "user": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+919876543210"
  },
  "callback_url": "https://api.namate24.com/api/wallet/payment-callback",
  "redirect": {
    "success_url": "https://namate24.com/student/wallet?payment=success",
    "cancel_url": "https://namate24.com/student/wallet?payment=cancelled"
  }
}
```

---

### 8. Verify Payment

**Endpoint:** `POST /api/wallet/verify-payment`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "transaction_id": 25,
  "razorpay_payment_id": "pay_MN4xYZ123456",
  "razorpay_order_id": "order_MN4xYZ123456",
  "razorpay_signature": "abc123xyz456..."
}
```

**Response:**
```json
{
  "message": "Payment successful! Coins credited.",
  "coins_added": 110,
  "balance": 135
}
```

---

### 9. Get Payment History

**Endpoint:** `GET /api/wallet/payment-history`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- `type` - Filter by transaction type (purchase, referral_bonus, referral_reward, admin_credit)
- `status` - Filter by status (pending, completed, failed)
- `search` - Search by description or payment ID
- `from_date` - Start date (YYYY-MM-DD)
- `to_date` - End date (YYYY-MM-DD)
- `per_page` - Results per page (default: 20)

**Example:** `GET /api/wallet/payment-history?type=purchase&status=completed&per_page=10`

**Response:**
```json
{
  "transactions": {
    "data": [
      {
        "id": 25,
        "type": "purchase",
        "amount": 110,
        "balance_after": 135,
        "description": "Purchased Starter Pack - 100 coins + 10 bonus",
        "payment_id": "pay_MN4xYZ123456",
        "order_id": "order_MN4xYZ123456",
        "meta": {
          "package_id": 1,
          "package_name": "Starter Pack",
          "coins": 100,
          "bonus_coins": 10,
          "price": 99,
          "status": "completed"
        },
        "created_at": "2025-12-19T12:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "per_page": 10,
    "total": 5
  },
  "stats": {
    "total_spent": 99,
    "total_earned": 25,
    "total_purchases": 1,
    "failed_payments": 0
  }
}
```

---

### 10. Get Order Status

**Endpoint:** `GET /api/wallet/order/{orderId}/status`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "order_id": "order_MN4xYZ123456",
  "payment_id": "pay_MN4xYZ123456",
  "status": "completed",
  "amount": 110,
  "description": "Purchased Starter Pack - 100 coins + 10 bonus",
  "created_at": "2025-12-19T12:00:00.000000Z",
  "updated_at": "2025-12-19T12:01:30.000000Z",
  "meta": {
    "package_id": 1,
    "coins": 100,
    "bonus_coins": 10,
    "status": "completed"
  }
}
```

---

### 11. Cancel Pending Payment

**Endpoint:** `POST /api/wallet/order/{orderId}/cancel`

**Headers:** `Authorization: Bearer {token}`

**Response:**
```json
{
  "message": "Payment cancelled successfully",
  "order_id": "order_MN4xYZ123456"
}
```

---

## Database Schema

### users Table
```sql
- id (bigint, primary key)
- name (varchar)
- email (varchar, nullable)
- phone (varchar, nullable)
- password (varchar)
- role (enum: student, tutor, admin)
- coins (integer, default: 0)
- referral_code (varchar, unique, indexed)
- referred_by (bigint, nullable, foreign key to users.id)
- created_at (timestamp)
- updated_at (timestamp)
```

### referrals Table
```sql
- id (bigint, primary key)
- referrer_id (bigint, foreign key to users.id)
- referred_id (bigint, foreign key to users.id)
- referrer_coins (integer)
- referred_coins (integer)
- reward_given (boolean)
- reward_given_at (timestamp, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### coin_transactions Table
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key to users.id)
- type (enum: purchase, referral_bonus, referral_reward, admin_credit, deduction)
- amount (integer)
- balance_after (integer)
- description (text)
- payment_id (varchar, nullable)
- order_id (varchar, nullable)
- meta (json, nullable)
- created_at (timestamp)
- updated_at (timestamp)
```

### coin_packages Table
```sql
- id (bigint, primary key)
- name (varchar)
- coins (integer)
- bonus_coins (integer, default: 0)
- price (decimal)
- is_active (boolean, default: true)
- sort_order (integer, default: 0)
- created_at (timestamp)
- updated_at (timestamp)
```

---

## Transaction Types

1. **purchase** - Buying coin packages
2. **referral_bonus** - Welcome bonus for new users using referral code
3. **referral_reward** - Reward for referrer when their code is used
4. **admin_credit** - Manual coin addition by admin
5. **deduction** - Coin spending (lessons, bookings, etc.)

---

## Business Rules

### Referral System
1. Each user gets a unique 8-character referral code upon registration
2. Referral code can be applied during registration or immediately after
3. Once a referral code is used, it cannot be changed
4. Users cannot use their own referral code
5. Unlimited referrals allowed per user
6. Coins are credited immediately upon successful referral

### Coin Wallet
1. Coins start at 0 for new users (unless referral bonus applied)
2. Coin balance is updated in real-time
3. All transactions are recorded with complete audit trail
4. Failed payments are tracked but don't affect balance
5. Payment verification uses Razorpay signature validation
6. Webhook handlers ensure payment status is always synchronized

---

## Security Features

1. **JWT Authentication**: All wallet endpoints require authentication
2. **Signature Verification**: Razorpay payments verified with HMAC SHA256
3. **Transaction Validation**: User can only access their own transactions
4. **Duplicate Prevention**: Payment ID checked to prevent double-crediting
5. **Webhook Security**: Webhook signature validation (if configured)
6. **Database Transactions**: All coin operations wrapped in DB transactions

---

## Frontend Integration

### Registration Flow with Referral

```javascript
// 1. Validate referral code (optional, before showing registration form)
const validateReferral = async (code) => {
  const response = await fetch('/api/validate-referral-code', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ referral_code: code })
  });
  return await response.json();
};

// 2. Register with referral code
const register = async (userData) => {
  const response = await fetch('/api/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      name: userData.name,
      email: userData.email,
      phone: userData.phone,
      password: userData.password,
      role: userData.role,
      referral_code: userData.referralCode // Optional
    })
  });
  
  const data = await response.json();
  
  if (data.referral_applied) {
    // Show success message with coins earned
    console.log(`You earned ${data.coins} coins!`);
  }
  
  return data;
};
```

### Wallet Display

```javascript
// Get wallet balance and stats
const fetchWallet = async (token) => {
  const response = await fetch('/api/wallet', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  return await response.json();
};

// Display format
// Balance: 175 coins
// Referrals: 3 friends (150 coins earned)
// Referral Code: ABC12345
// Share Link: https://namate24.com/register?ref=ABC12345
```

### Razorpay Integration

```javascript
// 1. Create order
const purchaseCoins = async (packageId, token) => {
  const response = await fetch('/api/wallet/purchase', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({ package_id: packageId })
  });
  return await response.json();
};

// 2. Open Razorpay checkout
const openCheckout = (orderData, token) => {
  const options = {
    key: RAZORPAY_KEY,
    amount: orderData.order.amount,
    currency: orderData.order.currency,
    name: 'Namate24',
    description: orderData.package.name,
    order_id: orderData.order.id,
    handler: async (response) => {
      // Verify payment
      await verifyPayment({
        transaction_id: orderData.transaction_id,
        razorpay_payment_id: response.razorpay_payment_id,
        razorpay_order_id: response.razorpay_order_id,
        razorpay_signature: response.razorpay_signature
      }, token);
    },
    prefill: {
      name: orderData.user.name,
      email: orderData.user.email,
      contact: orderData.user.phone
    }
  };
  
  const rzp = new Razorpay(options);
  rzp.open();
};

// 3. Verify payment
const verifyPayment = async (paymentData, token) => {
  const response = await fetch('/api/wallet/verify-payment', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(paymentData)
  });
  
  const data = await response.json();
  console.log(`${data.coins_added} coins added! New balance: ${data.balance}`);
  return data;
};
```

---

## Testing Checklist

### Registration with Referral
- [ ] Register without referral code
- [ ] Register with valid referral code
- [ ] Register with invalid referral code
- [ ] Register with own referral code (should fail)
- [ ] Verify coins credited to both users
- [ ] Check transaction history for both users

### Referral System
- [ ] Generate referral link
- [ ] Share referral code
- [ ] Validate referral code
- [ ] View referral stats
- [ ] View list of referred users
- [ ] Apply referral post-registration
- [ ] Attempt duplicate referral (should fail)

### Wallet Operations
- [ ] View balance
- [ ] View transaction history
- [ ] Filter transactions by type
- [ ] Search transactions
- [ ] View coin packages
- [ ] Purchase coins
- [ ] Verify payment
- [ ] Handle failed payment
- [ ] Cancel pending payment
- [ ] View order status

---

## Configuration

### Environment Variables (.env)

```env
# Razorpay
RAZORPAY_KEY=rzp_test_xxxxxxxxxxxxx
RAZORPAY_SECRET=xxxxxxxxxxxxxxxxxxxxx
RAZORPAY_WEBHOOK_SECRET=xxxxxxxxxxxxx (optional)

# Frontend URL (for referral links)
FRONTEND_URL=https://namate24.com
```

### Config File (config/services.php)

```php
'razorpay' => [
    'key' => env('RAZORPAY_KEY'),
    'secret' => env('RAZORPAY_SECRET'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
],
```

---

## Support & Troubleshooting

### Common Issues

**Issue: Coins not credited after payment**
- Check webhook logs: `storage/logs/laravel.log`
- Verify Razorpay signature
- Check transaction status in database
- Ensure webhook URL is configured in Razorpay dashboard

**Issue: Referral code not working**
- Verify code exists in database
- Check if user already used a referral
- Ensure referrer and referred are different users

**Issue: Duplicate coin credits**
- Check `payment_id` field in transactions
- Review webhook duplicate prevention logic
- Verify transaction database locks

---

## Future Enhancements

1. **Referral Tiers**: Earn bonus for referrals who make purchases
2. **Expiring Coins**: Set expiry dates for promotional coins
3. **Coin Gifting**: Allow users to send coins to friends
4. **Leaderboard**: Top referrers of the month
5. **Push Notifications**: Alert users when they earn coins
6. **Promotional Codes**: Admin-created discount codes
7. **Coin Bundles**: Special limited-time offers

---

## API Summary

| Method | Endpoint | Auth | Purpose |
|--------|----------|------|---------|
| POST | `/api/register` | No | Register with optional referral code |
| POST | `/api/validate-referral-code` | No | Validate referral code |
| GET | `/api/wallet` | Yes | Get wallet balance & transactions |
| GET | `/api/wallet/referral` | Yes | Get referral info & stats |
| POST | `/api/wallet/apply-referral` | Yes | Apply referral post-registration |
| GET | `/api/wallet/packages` | Yes | Get coin packages |
| POST | `/api/wallet/purchase` | Yes | Create purchase order |
| POST | `/api/wallet/verify-payment` | Yes | Verify payment & credit coins |
| GET | `/api/wallet/payment-history` | Yes | Get payment history |
| GET | `/api/wallet/order/{id}/status` | Yes | Get order status |
| POST | `/api/wallet/order/{id}/cancel` | Yes | Cancel pending payment |
| POST | `/api/wallet/webhook` | No | Razorpay webhook |
| GET | `/api/wallet/payment-callback` | No | Payment redirect callback |

---

**Last Updated:** December 19, 2025
**Version:** 1.0.0
**Status:** ✅ Production Ready
