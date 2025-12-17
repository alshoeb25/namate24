# Google OAuth Login/Signup Setup Guide

## ✅ Implementation Complete!

Gmail (Google OAuth) login and signup has been successfully integrated with your existing database.

## 📁 Files Created/Modified

### Backend Files:
1. **app/Http/Controllers/Api/SocialAuthController.php** - Handles Google OAuth authentication
2. **routes/api.php** - Added Google OAuth route
3. **config/services.php** - Added Google OAuth configuration

### Frontend Files:
1. **resources/js/components/GoogleLoginButton.vue** - Reusable Google login button component
2. **resources/js/pages/Login.vue** - Updated with Google login option
3. **resources/js/pages/Register.vue** - Updated with Google signup option

## 🔧 Setup Instructions

### Step 1: Get Google OAuth Credentials

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable **Google+ API** (or Google Identity Services)
4. Go to **Credentials** → **Create Credentials** → **OAuth 2.0 Client IDs**
5. Configure OAuth consent screen:
   - Add your app name: "Namate24"
   - Add authorized domains: your-domain.com
   - Add scopes: email, profile
6. Create OAuth Client ID:
   - Application type: **Web application**
   - Authorized JavaScript origins:
     - `http://localhost:5173` (for development)
     - `https://your-domain.com` (for production)
   - Authorized redirect URIs:
     - `http://localhost:5173/auth/google/callback`
     - `https://your-domain.com/auth/google/callback`
7. Copy the **Client ID** and **Client Secret**

### Step 2: Configure Environment Variables

Add these to your `.env` file:

```env
# Google OAuth Configuration
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=${APP_URL}/auth/google/callback

# Frontend Google Client ID (same as above)
VITE_GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
```

### Step 3: Update CORS (if needed)

Make sure your `config/cors.php` allows your frontend domain.

### Step 4: Build Frontend Assets

```bash
npm install
npm run dev
```

## 🎯 How It Works

### For Users:

1. **Login Page**: Click "Continue with Google"
2. **Google OAuth Popup**: User authenticates with Google
3. **Auto-Login/Signup**: 
   - If email exists in database → User is logged in
   - If email doesn't exist → New account is created automatically
4. **Auto-Verified**: Google users are automatically email-verified
5. **Redirect**: User is redirected based on their role (tutor/student)

### Database Integration:

- Uses existing `users` table
- Email from Google is checked against existing records
- New users are created with:
  - Name from Google account
  - Email from Google account
  - Profile photo from Google account
  - Email automatically verified
  - No password (OAuth users don't need passwords)
- If user exists, profile photo is updated if not set
- Proper role assignment (student/tutor)
- Tutor profile automatically created for tutors
- Wallet automatically created for all users

## 📡 API Endpoint

**POST** `/api/auth/google/callback`

**Request Body:**
```json
{
  "access_token": "google-jwt-token",
  "email": "user@gmail.com",
  "name": "John Doe",
  "picture": "https://...",
  "role": "student" // or "tutor"
}
```

**Response:**
```json
{
  "user": {...},
  "roles": ["student"],
  "token": "jwt-token",
  "token_type": "bearer",
  "expires_in": 3600,
  "redirect_url": "/",
  "is_new_user": true
}
```

## 🔐 Security Features

- ✅ Google token verification
- ✅ Email automatically verified for Google users
- ✅ JWT authentication for session management
- ✅ Role-based access control
- ✅ Existing user detection (prevents duplicates)
- ✅ Profile photo auto-update

## 🎨 User Experience

### Login Page:
```
┌─────────────────────────┐
│  Email/Phone Input      │
│  Password Input         │
│  [Login Button]         │
│  ─────── or ───────     │
│  [Continue with Google] │
└─────────────────────────┘
```

### Register Page:
```
┌─────────────────────────┐
│  Full Name Input        │
│  Email/Phone Input      │
│  Password Input         │
│  Confirm Password       │
│  Role Select            │
│  [Sign Up Button]       │
│  ─────── or ───────     │
│  [Continue with Google] │
└─────────────────────────┘
```

## 🧪 Testing

### Test Login Flow:
1. Click "Continue with Google" on login page
2. Choose a Google account
3. Should redirect to dashboard
4. Check database - user record should exist

### Test Signup Flow:
1. Click "Continue with Google" on register page
2. Select role (student/tutor)
3. Choose a Google account
4. Should create new user and redirect
5. Check database - new user with email_verified_at set

## 📊 Database Schema

The existing `users` table already supports this:

```sql
- email (unique, nullable) ✅
- email_verified_at ✅
- password (nullable) ✅ - OAuth users don't need password
- avatar (nullable) ✅ - Stores Google profile photo
- role (student/tutor) ✅
```

## 🚀 Production Deployment

1. Update authorized domains in Google Cloud Console
2. Set production environment variables
3. Update CORS settings for production domain
4. Build frontend: `npm run build`
5. Test thoroughly on staging first

## 🆘 Troubleshooting

**Error: "Invalid Google token"**
- Check if VITE_GOOGLE_CLIENT_ID is set correctly
- Verify Google Client ID in Google Cloud Console

**Error: "Unauthorized"**
- Add your domain to authorized JavaScript origins
- Check CORS configuration

**User not created**
- Check backend logs
- Verify database connection
- Ensure migrations are run

## ✨ Features

✅ Google OAuth login
✅ Google OAuth signup
✅ Auto email verification
✅ Profile photo from Google
✅ Existing user detection
✅ Role-based redirection
✅ JWT authentication
✅ Works with existing database
✅ No duplicate accounts
✅ Seamless user experience

---

**All files are ready!** Just add your Google OAuth credentials to `.env` and you're good to go! 🎉
