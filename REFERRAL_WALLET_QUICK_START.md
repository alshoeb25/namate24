# Referral & Wallet System - Quick Start Guide

## ✅ Backend Implementation Complete

The referral and wallet system is fully implemented with the following features:

### 🎁 Referral System
- **50 coins** per successful referral for the referrer
- **25 welcome coins** for new users using referral code
- **Unlimited referrals** - no limit on referrals per user
- Auto-generated unique referral codes for all users
- Referral validation during registration
- Post-registration referral code application

### 💰 Wallet System
- Coin balance tracking
- Transaction history with filters
- Razorpay payment integration
- Coin packages with bonus coins
- Payment verification and webhooks
- Order status tracking and cancellation

---

## 🚀 Quick Test Guide

### 1. Test Registration with Referral Code

#### Step 1: Create First User (Referrer)
```bash
POST http://localhost:8000/api/register
Content-Type: application/json

{
  "name": "Alice Referrer",
  "email": "alice@example.com",
  "phone": "+919876543210",
  "password": "password123",
  "role": "student"
}
```

**Response:** Note the `referral_code` in response (e.g., "ABC12345")

---

#### Step 2: Validate Referral Code
```bash
POST http://localhost:8000/api/validate-referral-code
Content-Type: application/json

{
  "referral_code": "ABC12345"
}
```

**Expected Response:**
```json
{
  "valid": true,
  "message": "Valid referral code",
  "referrer": {
    "name": "Alice Referrer",
    "referral_code": "ABC12345"
  },
  "reward": {
    "referrer_coins": 50,
    "referred_coins": 25
  }
}
```

---

#### Step 3: Register New User with Referral Code
```bash
POST http://localhost:8000/api/register
Content-Type: application/json

{
  "name": "Bob Referred",
  "email": "bob@example.com",
  "phone": "+919876543211",
  "password": "password123",
  "role": "student",
  "referral_code": "ABC12345"
}
```

**Expected Response:**
```json
{
  "message": "Registration successful! You earned 25 coins from the referral!",
  "user": {
    "id": 2,
    "name": "Bob Referred",
    "email": "bob@example.com",
    "coins": 25,
    "referral_code": "XYZ98765",
    "referred_by": 1
  },
  "referral_applied": true,
  "referral_reward": {
    "coins": 25,
    "referrer_name": "Alice Referrer"
  },
  "coins": 25
}
```

---

### 2. Test Wallet Endpoints

#### Step 1: Login as Alice (Referrer)
```bash
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "alice@example.com",
  "password": "password123"
}
```

**Copy the `token` from response**

---

#### Step 2: Check Wallet Balance
```bash
GET http://localhost:8000/api/wallet
Authorization: Bearer {token}
```

**Expected Response:**
```json
{
  "balance": 50,
  "referral_code": "ABC12345",
  "referral_stats": {
    "total_referrals": 1,
    "coins_earned": 50
  },
  "transactions": {
    "data": [
      {
        "type": "referral_reward",
        "amount": 50,
        "description": "Referral reward for Bob Referred"
      }
    ]
  }
}
```

---

#### Step 3: Get Referral Information
```bash
GET http://localhost:8000/api/wallet/referral
Authorization: Bearer {token}
```

**Expected Response:**
```json
{
  "stats": {
    "total_referrals": 1,
    "total_coins_earned": 50,
    "referral_code": "ABC12345",
    "referral_link": "http://localhost:3000/register?ref=ABC12345"
  },
  "referrals": [
    {
      "referrer_coins": 50,
      "referred_coins": 25,
      "referred": {
        "name": "Bob Referred",
        "email": "bob@example.com"
      }
    }
  ]
}
```

---

### 3. Test Post-Registration Referral Application

#### Step 1: Register User WITHOUT Referral Code
```bash
POST http://localhost:8000/api/register
Content-Type: application/json

{
  "name": "Charlie NoRef",
  "email": "charlie@example.com",
  "password": "password123",
  "role": "tutor"
}
```

**User starts with 0 coins**

---

#### Step 2: Login as Charlie
```bash
POST http://localhost:8000/api/login
Content-Type: application/json

{
  "email": "charlie@example.com",
  "password": "password123"
}
```

---

#### Step 3: Apply Referral Code
```bash
POST http://localhost:8000/api/wallet/apply-referral
Authorization: Bearer {charlie_token}
Content-Type: application/json

{
  "referral_code": "ABC12345"
}
```

**Expected Response:**
```json
{
  "message": "Referral applied! You earned 25 coins.",
  "coins_earned": 25,
  "balance": 25
}
```

---

### 4. Test Coin Purchase Flow

#### Step 1: Get Available Packages
```bash
GET http://localhost:8000/api/wallet/packages
Authorization: Bearer {token}
```

**If no packages exist, create them in Filament admin or database**

---

#### Step 2: Purchase Coins
```bash
POST http://localhost:8000/api/wallet/purchase
Authorization: Bearer {token}
Content-Type: application/json

{
  "package_id": 1
}
```

**Expected Response:** Razorpay order details

---

#### Step 3: Verify Payment (After Razorpay payment)
```bash
POST http://localhost:8000/api/wallet/verify-payment
Authorization: Bearer {token}
Content-Type: application/json

{
  "transaction_id": 25,
  "razorpay_payment_id": "pay_xxxxx",
  "razorpay_order_id": "order_xxxxx",
  "razorpay_signature": "signature_xxxxx"
}
```

---

### 5. Test Payment History

```bash
GET http://localhost:8000/api/wallet/payment-history
Authorization: Bearer {token}
```

**With Filters:**
```bash
GET http://localhost:8000/api/wallet/payment-history?type=referral_reward&per_page=10
Authorization: Bearer {token}
```

---

## 🧪 Testing Scenarios

### Scenario 1: Valid Referral Flow
1. ✅ User A registers → Gets referral code
2. ✅ User B validates referral code → Code is valid
3. ✅ User B registers with referral code
4. ✅ User A receives 50 coins (referral_reward)
5. ✅ User B receives 25 coins (referral_bonus)
6. ✅ Both transactions recorded in database

### Scenario 2: Invalid Referral Attempts
1. ❌ User tries to use own referral code → Fails
2. ❌ User tries to use invalid code → Fails
3. ❌ User tries to apply referral twice → Fails
4. ❌ User tries to use referral code of non-existent user → Fails

### Scenario 3: Multiple Referrals
1. ✅ User A refers User B → Earns 50 coins
2. ✅ User A refers User C → Earns another 50 coins (Total: 100)
3. ✅ User A refers User D → Earns another 50 coins (Total: 150)
4. ✅ All referrals tracked in database
5. ✅ Referral stats show correct totals

### Scenario 4: Wallet Operations
1. ✅ Check balance → Shows correct amount
2. ✅ View transaction history → All transactions listed
3. ✅ Filter by type → Only matching transactions shown
4. ✅ Purchase coins → Order created
5. ✅ Verify payment → Coins credited
6. ✅ View updated balance → Reflects new coins

---

## 📊 Database Verification Queries

### Check User Coins and Referral Codes
```sql
SELECT id, name, email, coins, referral_code, referred_by 
FROM users;
```

### Check All Referrals
```sql
SELECT r.*, 
       u1.name as referrer_name, 
       u2.name as referred_name
FROM referrals r
JOIN users u1 ON r.referrer_id = u1.id
JOIN users u2 ON r.referred_id = u2.id;
```

### Check Coin Transactions
```sql
SELECT ct.*, u.name as user_name
FROM coin_transactions ct
JOIN users u ON ct.user_id = u.id
ORDER BY ct.created_at DESC;
```

### Verify Referral Coins Match
```sql
SELECT 
    u.id,
    u.name,
    u.coins,
    COALESCE(SUM(ct.amount), 0) as transaction_total
FROM users u
LEFT JOIN coin_transactions ct ON u.id = ct.user_id
GROUP BY u.id, u.name, u.coins;
```

---

## 🔧 Environment Setup

### Required in .env
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=namate24
DB_USERNAME=root
DB_PASSWORD=

# JWT
JWT_SECRET=your_jwt_secret

# Razorpay
RAZORPAY_KEY=rzp_test_xxxxx
RAZORPAY_SECRET=xxxxx
RAZORPAY_WEBHOOK_SECRET=xxxxx

# Frontend URL (for referral links)
FRONTEND_URL=http://localhost:3000
```

---

## 📝 API Endpoints Summary

| Endpoint | Method | Auth | Purpose |
|----------|--------|------|---------|
| `/api/register` | POST | No | Register with optional referral code |
| `/api/validate-referral-code` | POST | No | Validate referral code before registration |
| `/api/login` | POST | No | Login and get JWT token |
| `/api/wallet` | GET | Yes | Get wallet balance & transactions |
| `/api/wallet/referral` | GET | Yes | Get referral info & stats |
| `/api/wallet/apply-referral` | POST | Yes | Apply referral code post-registration |
| `/api/wallet/packages` | GET | Yes | Get available coin packages |
| `/api/wallet/purchase` | POST | Yes | Create coin purchase order |
| `/api/wallet/verify-payment` | POST | Yes | Verify Razorpay payment |
| `/api/wallet/payment-history` | GET | Yes | Get payment history with filters |
| `/api/wallet/order/{id}/status` | GET | Yes | Get order status |
| `/api/wallet/order/{id}/cancel` | POST | Yes | Cancel pending payment |

---

## 🐛 Common Issues & Solutions

### Issue 1: "Referral code not found"
**Solution:** Ensure user has been created and has a referral_code in database

### Issue 2: "Already used a referral code"
**Solution:** Check `referred_by` field in users table - user can only use one referral code

### Issue 3: "Coins not credited"
**Solution:** Check coin_transactions table for transaction records and verify amounts

### Issue 4: "Payment verification failed"
**Solution:** 
- Check Razorpay keys in .env
- Verify signature calculation
- Check webhook logs

### Issue 5: "Transaction not found"
**Solution:** Ensure order was created first via `/api/wallet/purchase`

---

## 🎯 Next Steps

### For Frontend Integration:
1. Create registration form with referral code field
2. Add referral code validation on input
3. Display success message with coins earned
4. Create wallet page showing balance and transactions
5. Add referral sharing component with copy button
6. Implement Razorpay checkout integration
7. Create payment success/failure pages

### For Testing:
1. Use Postman/Insomnia to test all endpoints
2. Verify database records after each operation
3. Test edge cases (invalid codes, duplicate attempts)
4. Load test with multiple concurrent referrals
5. Test Razorpay test mode payments

### For Production:
1. Switch to Razorpay live keys
2. Set up proper webhook URL
3. Configure frontend URL correctly
4. Enable email notifications for referrals
5. Add rate limiting for registration
6. Set up monitoring for failed payments
7. Create admin dashboard for referral analytics

---

## 📞 Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Review API documentation: `REFERRAL_WALLET_API.md`
- Database migrations: `database/migrations/`
- Controller code: `app/Http/Controllers/Api/WalletController.php` and `AuthController.php`

---

**Status:** ✅ Backend Complete & Ready for Testing
**Last Updated:** December 19, 2025
