# Tutor Login → Dashboard Redirect - Quick Start Guide

## What Was Implemented

When a tutor logs in (via API or web), they are automatically redirected to their personalized dashboard.

---

## How It Works

### **Step 1: Tutor Registers or Logs In**

```bash
# Register
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "role": "tutor"
}

# Login
POST /api/login
{
  "email": "john@example.com",
  "password": "password123"
}
```

### **Step 2: Backend Creates Tutor Profile**

```php
// In AuthController::register()
if ($data['role'] === 'tutor') {
    Tutor::create(['user_id' => $user->id]);  // ✓ Auto-created
}
```

### **Step 3: API Returns Redirect URL**

**Response (both register & login):**
```json
{
  "user": { ... },
  "token": "eyJ0eXAiOiJKV1QiL...",
  "redirect_url": "/tutor/profile/",  // ← NEW!
  "token_type": "bearer",
  "expires_in": 3600
}
```

### **Step 4: Frontend Redirects to Dashboard**

```javascript
// Example: Vue.js / React
const response = await axios.post('/api/login', credentials);

// Store token for future requests
localStorage.setItem('token', response.data.token);
axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;

// Redirect to dashboard
window.location.href = response.data.redirect_url;  // /tutor/profile/
```

### **Step 5: Dashboard Page Loads**

```
GET /tutor/profile/
↓
RedirectAfterLogin middleware checks role ✓
↓
ProfileController::dashboard() renders
↓
Tutor sees their dashboard with:
  - Profile completion percentage
  - Quick-access profile section cards
  - Navigation to all profile pages
```

---

## Files Modified

| File | Change |
|------|--------|
| `app/Http/Kernel.php` | Added `RedirectAfterLogin` middleware to global stack + Spatie role middleware aliases |
| `app/Http/Middleware/RedirectAfterLogin.php` | **NEW** - Redirects tutor/admin to their dashboard |
| `app/Http/Controllers/Api/AuthController.php` | Added `redirect_url` to register & login responses, auto-create Tutor record |
| `routes/tutor.php` | Unchanged - dashboard route already present |

---

## Test It

### **Web Browser Flow**

```bash
1. Start server
   php artisan serve

2. Navigate to login page / signup
3. Register/Login as tutor
4. Should redirect to: http://localhost:8000/tutor/profile/
5. See dashboard with profile cards
```

### **API Testing (cURL)**

```bash
# Register tutor
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Tutor",
    "email": "jane@example.com",
    "password": "pass123",
    "role": "tutor"
  }'

# Extract token and redirect_url from response
# Use token in Authorization header for API calls
# Navigate frontend to redirect_url

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "jane@example.com",
    "password": "pass123"
  }'
```

---

## Security Features

✅ **Role-based routing** - Only tutors can access `/tutor/profile/*`
✅ **Middleware protection** - `role:tutor` middleware on all dashboard routes
✅ **JWT validation** - API verifies Bearer token on every request
✅ **Auto-profile creation** - Tutor record created during registration
✅ **Session-based fallback** - Works with both API and traditional web login

---

## Next: Run Database Migration

Before testing in browser, run migrations to ensure all tables exist:

```bash
php artisan migrate
php artisan view:clear
php artisan cache:clear
```

---

## Frontend Integration Example

### **Vue.js Component**

```vue
<template>
  <form @submit.prevent="login">
    <input v-model="email" type="email" placeholder="Email" />
    <input v-model="password" type="password" placeholder="Password" />
    <button :disabled="loading">{{ loading ? 'Logging in...' : 'Login' }}</button>
  </form>
</template>

<script>
export default {
  data() {
    return { email: '', password: '', loading: false };
  },
  methods: {
    async login() {
      this.loading = true;
      try {
        const response = await axios.post('/api/login', {
          email: this.email,
          password: this.password,
        });
        
        // Store token
        localStorage.setItem('token', response.data.token);
        axios.defaults.headers.common['Authorization'] = 
          `Bearer ${response.data.token}`;
        
        // Redirect to dashboard
        window.location.href = response.data.redirect_url;
      } catch (err) {
        alert(err.response.data.message);
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
```

### **React Hook**

```jsx
import { useState } from 'react';
import axios from 'axios';

function LoginForm() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);

  const handleLogin = async (e) => {
    e.preventDefault();
    setLoading(true);
    try {
      const response = await axios.post('/api/login', { email, password });
      
      localStorage.setItem('token', response.data.token);
      axios.defaults.headers.common['Authorization'] = 
        `Bearer ${response.data.token}`;
      
      window.location.href = response.data.redirect_url;
    } catch (err) {
      alert(err.response?.data?.message || 'Login failed');
    } finally {
      setLoading(false);
    }
  };

  return (
    <form onSubmit={handleLogin}>
      <input 
        type="email" 
        value={email} 
        onChange={(e) => setEmail(e.target.value)}
        placeholder="Email"
      />
      <input 
        type="password" 
        value={password} 
        onChange={(e) => setPassword(e.target.value)}
        placeholder="Password"
      />
      <button disabled={loading}>{loading ? 'Logging in...' : 'Login'}</button>
    </form>
  );
}
```

---

## Summary

✨ **After tutor login:**
1. Backend creates tutor profile record automatically
2. API returns JWT token + redirect URL
3. Frontend stores token & redirects to `/tutor/profile/`
4. Middleware verifies role & token
5. Dashboard loads with profile completion UI
6. Tutor can edit all profile sections

🎯 **Status:** ✅ Ready for testing!
