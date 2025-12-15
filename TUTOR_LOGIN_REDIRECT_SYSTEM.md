# Tutor Dashboard Redirect System - Implementation Complete

## Overview
After a tutor logs in, they are automatically redirected to their personalized dashboard at `/tutor/profile/` where they can manage their profile, courses, education, and other details.

---

## Implementation Components

### 1. **HTTP Kernel Middleware Registration** 
**File:** `app/Http/Kernel.php`

**What's New:**
- Added `\App\Http\Middleware\RedirectAfterLogin::class` to global middleware stack
- Registered Spatie permission middleware aliases:
  - `'role' => RoleMiddleware::class`
  - `'permission' => PermissionMiddleware::class`
  - `'role_or_permission' => RoleOrPermissionMiddleware::class`

**Purpose:** Ensures all routes check user role and redirect after authentication.

---

### 2. **Redirect After Login Middleware**
**File:** `app/Http/Middleware/RedirectAfterLogin.php` (NEW)

**Functionality:**
```php
// Tutor → /tutor/profile/ (dashboard)
// Admin → /admin (admin panel)
// Others → / (home)
```

**When It Runs:** On every HTTP request globally (in `$middleware` stack)

---

### 3. **API Authentication Controller**
**File:** `app/Http/Controllers/Api/AuthController.php`

**Updated Methods:**

#### `register()` - Enhanced
```php
// Now automatically:
1. Creates User with role field
2. Creates Wallet for transactions
3. Creates Tutor profile if role='tutor'
4. Returns redirect_url in JSON response
```

**Response Example:**
```json
{
  "user": { "id": 1, "name": "John", "role": "tutor", ... },
  "token": "eyJ0eXAiOiJKV1QiLC...",
  "token_type": "bearer",
  "expires_in": 3600,
  "redirect_url": "/tutor/profile/"
}
```

#### `login()` - Enhanced
```php
// Now returns redirect_url based on role:
// tutor → /tutor/profile/ (dashboard)
// admin → /admin (admin panel)
// default → /home
```

**Response Example:**
```json
{
  "user": { "id": 1, "name": "John", "role": "tutor", ... },
  "token": "eyJ0eXAiOiJKV1QiLC...",
  "token_type": "bearer",
  "expires_in": 3600,
  "redirect_url": "/tutor/profile/"
}
```

---

### 4. **Web Routes**
**File:** `routes/tutor.php` (Already existed)

**Tutor Dashboard Route:**
```php
Route::middleware(['auth', 'role:tutor'])->prefix('tutor/profile')->group(function () {
    Route::get('/', [ProfileController::class, 'dashboard'])->name('tutor.profile.dashboard');
    // ... other profile routes
});
```

---

### 5. **Tutor Dashboard Blade View**
**File:** `resources/views/tutor/profile/dashboard.blade.php`

**Features:**
- ✓ Profile completion percentage (visual progress bar)
- ✓ Quick-access cards for all profile sections
- ✓ User avatar, name, headline, rating
- ✓ Navigation to all profile sections:
  - Personal Details
  - Photo
  - Video Introduction
  - Subjects
  - Address
  - Education
  - Experience
  - Teaching Details
  - Profile Description
  - Courses
  - Settings
  - View Public Profile

---

## Authentication Flow (Web)

```
┌─────────────┐
│   Login     │
│   Form      │
└──────┬──────┘
       │
       ▼
┌─────────────────────┐
│ POST /login (API)  │
│ or Web Login Route  │
└──────┬──────────────┘
       │
       ▼
┌──────────────────────────────┐
│ AuthController::login()      │
│ - Validate credentials       │
│ - Generate JWT token         │
│ - Get redirect URL           │
│ - Return user + token + URL  │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Client receives response:    │
│ {                            │
│   user: {...},              │
│   token: "...",             │
│   redirect_url: "/tutor/..."|
│ }                            │
└──────┬───────────────────────┘
       │
       ▼ (Frontend redirects)
┌─────────────────────────────────┐
│ GET /tutor/profile/             │
│ (with Bearer token in header)   │
└──────┬──────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ RedirectAfterLogin Middleware    │
│ - Checks if user is logged in    │
│ - Verifies role                  │
│ - Allows request to proceed      │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ ProfileController::dashboard()   │
│ - Gets authenticated tutor       │
│ - Calculates completion %        │
│ - Returns dashboard view         │
└──────┬───────────────────────────┘
       │
       ▼
┌──────────────────────────────────┐
│ Tutor Dashboard Page             │
│ - Profile header                 │
│ - Progress bar                   │
│ - Quick-access cards             │
│ - Navigation links               │
└──────────────────────────────────┘
```

---

## API Integration (for Frontend)

### Register a New Tutor
```bash
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "password": "SecurePass123",
  "role": "tutor"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "tutor"
  },
  "token": "eyJ0eXAiOiJKV1QiL...",
  "token_type": "bearer",
  "expires_in": 3600,
  "redirect_url": "/tutor/profile/"
}
```

### Login Tutor
```bash
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "SecurePass123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "role": "tutor"
  },
  "token": "eyJ0eXAiOiJKV1QiL...",
  "token_type": "bearer",
  "expires_in": 3600,
  "redirect_url": "/tutor/profile/"
}
```

### Frontend Implementation Example (Vue.js)
```javascript
// After successful login:
const response = await axios.post('/api/login', {
  email: 'john@example.com',
  password: 'password'
});

// Store token
localStorage.setItem('token', response.data.token);
axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;

// Redirect to dashboard
window.location.href = response.data.redirect_url;
// or with Vue Router:
this.$router.push(response.data.redirect_url);
```

---

## Web-Session Based Flow (Alternative)

For traditional web login (forms), the middleware automatically redirects:

```php
// In web.php or custom login controller:
if (auth()->check() && auth()->user()->role === 'tutor') {
    return redirect()->route('tutor.profile.dashboard');
}
```

The `RedirectAfterLogin` middleware handles this globally.

---

## Security

✓ **Role-based Access Control:** Only authenticated tutors can access `/tutor/profile/*`
✓ **JWT Validation:** API endpoints verify Bearer token
✓ **Middleware Stacking:** Multiple layers of auth checks
✓ **User Role Verification:** Tutor record created on registration

---

## Todo Tracking

- [x] Create HTTP Kernel with role middleware
- [x] Add API endpoints for tutor profile sections
- [x] Create Vue components for tutor profile
- [x] Implement tutor dashboard redirect after login
- [x] Register middleware in kernel
- [x] Update AuthController with redirect logic
- [ ] Test login → dashboard flow in browser
- [ ] Test API registration → dashboard redirect
- [ ] Test phone OTP integration
- [ ] Run database migration

---

## Next Steps

1. **Test Browser Flow:**
   ```bash
   php artisan serve
   # Navigate to login, fill tutor credentials
   # Should redirect to /tutor/profile/
   ```

2. **Test API Flow:**
   ```bash
   curl -X POST http://localhost:8000/api/login \
     -H "Content-Type: application/json" \
     -d '{
       "email": "tutor@example.com",
       "password": "password"
     }'
   ```

3. **Run Migrations:**
   ```bash
   php artisan migrate
   php artisan view:clear
   php artisan cache:clear
   ```

---

## Files Modified/Created

| File | Type | Purpose |
|------|------|---------|
| `app/Http/Kernel.php` | Modified | Added middleware + Spatie aliases |
| `app/Http/Middleware/RedirectAfterLogin.php` | Created | Global redirect logic |
| `app/Http/Controllers/Api/AuthController.php` | Modified | Added redirect_url to responses |
| `routes/tutor.php` | Existing | Dashboard route already present |
| `resources/views/tutor/profile/dashboard.blade.php` | Existing | Dashboard view ready |

---

**Status:** ✅ **Ready for Testing**
