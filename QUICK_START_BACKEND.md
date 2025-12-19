# 🚀 Quick Start - Backend Integration

## ⚡ 3-Minute Setup

```bash
# 1. Create roles (IMPORTANT!)
php artisan db:seed --class=RoleSeeder

# 2. Run migrations
php artisan migrate --force

# 3. Clear cache
php artisan cache:clear && php artisan config:clear

# 4. Test
curl -X POST http://localhost/namate24/public/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test","email":"test@example.com","password":"password123","role":"student"}'
```

## 📍 Key Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/user/enroll-teacher` | Add teacher role |
| `POST` | `/api/user/enroll-student` | Add student role |
| `GET` | `/api/user` | Get user with roles |
| `POST` | `/api/student/request-tutor` | Submit request |
| `GET` | `/api/student/requirements` | List requests |

## 🔑 Authentication

```javascript
// Frontend: After login, store token
localStorage.setItem('token', response.data.token);

// Use in API calls
headers: {
  'Authorization': `Bearer ${token}`
}
```

## ✅ Testing Checklist

- [ ] Register student → `POST /api/register`
- [ ] Login → `POST /api/login`
- [ ] Get user → `GET /api/user`
- [ ] Enroll as teacher → `POST /api/user/enroll-teacher`
- [ ] Submit request → `POST /api/student/request-tutor`
- [ ] View requests → `GET /api/student/requirements`

## 📦 Files Overview

```
Backend (Created):
├── app/Models/Student.php
├── app/Http/Controllers/Api/UserController.php
├── app/Http/Controllers/Api/StudentController.php
└── database/migrations/
    ├── 2025_12_19_000001_create_students_table.php
    └── 2025_12_19_000002_add_detailed_fields...php

Backend (Modified):
├── app/Models/User.php
├── app/Models/StudentRequirement.php
├── app/Http/Controllers/Api/AuthController.php
└── routes/api.php

Frontend (Already Complete):
├── components/EnrollmentModal.vue
├── pages/student/*.vue
└── store/index.js
```

## 🎯 Quick Test (Postman)

1. **Register**
   ```
   POST /api/register
   {"name":"John","email":"john@test.com","password":"pass123","role":"student"}
   ```

2. **Login** (save token)
   ```
   POST /api/login
   {"email":"john@test.com","password":"pass123"}
   ```

3. **Enroll as Teacher**
   ```
   POST /api/user/enroll-teacher
   Headers: Authorization: Bearer {token}
   ```

4. **Get User** (should have both roles)
   ```
   GET /api/user
   Headers: Authorization: Bearer {token}
   ```

## 🔧 Troubleshooting

| Issue | Solution |
|-------|----------|
| Migration fails | Check database connection in `.env` |
| Token invalid | Token expired, login again |
| Already enrolled error | Normal - user already has that role |
| CORS error | Add frontend URL to CORS config |

## 📚 Documentation

- **Complete API Guide**: `POSTMAN_API_TESTING_GUIDE.md`
- **Test Cases**: `DUAL_ROLE_TESTING_CHECKLIST.md`
- **Frontend Guide**: `DUAL_ROLE_QUICK_START.md`
- **Architecture**: `DUAL_ROLE_ARCHITECTURE_DIAGRAM.md`

## ✨ Status

✅ Backend: Complete
✅ Frontend: Complete  
✅ Integration: Ready
🔄 Testing: In Progress

---

**Need Help?** Check `BACKEND_INTEGRATION_COMPLETE.md` for full details.
