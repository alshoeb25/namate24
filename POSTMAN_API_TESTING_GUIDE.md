# 🔌 Dual Role System - API Testing Guide (Postman)

## Setup

### Base URL
```
http://localhost/namate24/public/api
```

### Headers (for authenticated requests)
```
Authorization: Bearer {your_jwt_token}
Content-Type: application/json
Accept: application/json
```

---

## 1. Authentication

### Register (Student)
```http
POST /register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "password": "password123",
  "role": "student"
}
```

**Response:**
```json
{
  "message": "Registration successful!",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "student": {
      "id": 1,
      "user_id": 1
    }
  }
}
```

### Register (Teacher)
```http
POST /register
Content-Type: application/json

{
  "name": "Jane Smith",
  "email": "jane@example.com",
  "phone": "0987654321",
  "password": "password123",
  "role": "tutor"
}
```

### Login
```http
POST /login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "tutor": null,
    "student": {
      "id": 1,
      "user_id": 1
    },
    "wallet": {
      "id": 1,
      "balance": 0
    }
  },
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

---

## 2. User Management

### Get User (with relationships)
```http
GET /user
Authorization: Bearer {token}
```

**Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890",
  "avatar": "avatars/abc123.jpg",
  "tutor": null,
  "student": {
    "id": 1,
    "user_id": 1,
    "grade_level": null,
    "learning_goals": null
  },
  "wallet": {
    "id": 1,
    "balance": 0
  }
}
```

---

## 3. Enrollment

### Enroll as Teacher
```http
POST /user/enroll-teacher
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Successfully enrolled as teacher!",
  "tutor": {
    "id": 5,
    "user_id": 1,
    "created_at": "2025-12-19T10:30:00.000000Z",
    "updated_at": "2025-12-19T10:30:00.000000Z"
  }
}
```

**Error (already enrolled):**
```json
{
  "message": "You are already enrolled as a teacher.",
  "tutor": {
    "id": 5,
    "user_id": 1
  }
}
```

### Enroll as Student
```http
POST /user/enroll-student
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Successfully enrolled as student!",
  "student": {
    "id": 3,
    "user_id": 1,
    "created_at": "2025-12-19T10:35:00.000000Z",
    "updated_at": "2025-12-19T10:35:00.000000Z"
  }
}
```

---

## 4. Profile Management

### Update Profile
```http
PUT /user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "John Updated",
  "email": "john.new@example.com",
  "phone": "9876543210"
}
```

**Response (email changed):**
```json
{
  "message": "Profile updated. Please verify your new email.",
  "user": {
    "id": 1,
    "name": "John Updated",
    "email": "john.new@example.com",
    "email_verified_at": null
  },
  "email_verification_required": true
}
```

### Upload Photo
```http
POST /user/photo
Authorization: Bearer {token}
Content-Type: multipart/form-data

photo: [file]
```

**Response:**
```json
{
  "message": "Photo uploaded successfully.",
  "avatar": "avatars/xyz789.jpg",
  "avatar_url": "http://localhost/namate24/storage/avatars/xyz789.jpg"
}
```

### Send Phone OTP
```http
POST /user/phone/send-otp
Authorization: Bearer {token}
Content-Type: application/json

{
  "phone": "9988776655"
}
```

**Response:**
```json
{
  "message": "OTP sent successfully.",
  "otp": "123456"
}
```

### Verify Phone OTP
```http
POST /user/phone/verify-otp
Authorization: Bearer {token}
Content-Type: application/json

{
  "phone": "9988776655",
  "otp": "123456"
}
```

**Response:**
```json
{
  "message": "Phone verified successfully.",
  "user": {
    "id": 1,
    "phone": "9988776655",
    "phone_verified_at": "2025-12-19T10:45:00.000000Z"
  }
}
```

---

## 5. Student - Request Tutor (12-Step Form)

### Submit Tutor Request
```http
POST /student/request-tutor
Authorization: Bearer {token}
Content-Type: application/json

{
  "location": "123 Main Street, New York",
  "lat": 40.7128,
  "lng": -74.0060,
  "phone": "1234567890",
  "details": "Looking for an experienced math tutor for my son. He needs help with algebra and geometry. Prefer someone with patience and good teaching skills.",
  "subjects": ["Mathematics", "Physics"],
  "level": "High School",
  "service_type": "both",
  "meeting_options": ["student_home", "online"],
  "budget": 500,
  "budget_type": "per_month",
  "gender_preference": "no_preference",
  "availability": ["monday", "wednesday", "friday"],
  "time_preference": "Evening (5 PM - 8 PM)",
  "languages": ["English", "Hindi"],
  "tutor_location_preference": "Within 5 km",
  "max_distance": 5
}
```

**Response:**
```json
{
  "message": "Tutor request submitted successfully!",
  "requirement": {
    "id": 10,
    "student_id": 1,
    "location": "123 Main Street, New York",
    "phone": "1234567890",
    "details": "Looking for an experienced math tutor...",
    "subjects": ["Mathematics", "Physics"],
    "level": "High School",
    "service_type": "both",
    "meeting_options": ["student_home", "online"],
    "budget": 500,
    "budget_type": "per_month",
    "gender_preference": "no_preference",
    "availability": ["monday", "wednesday", "friday"],
    "time_preference": "Evening (5 PM - 8 PM)",
    "languages": ["English", "Hindi"],
    "tutor_location_preference": "Within 5 km",
    "max_distance": 5,
    "status": "active",
    "created_at": "2025-12-19T11:00:00.000000Z"
  }
}
```

### Get All Requirements
```http
GET /student/requirements
Authorization: Bearer {token}
```

**Response:**
```json
{
  "requirements": [
    {
      "id": 10,
      "student_id": 1,
      "location": "123 Main Street, New York",
      "subjects": ["Mathematics", "Physics"],
      "level": "High School",
      "budget": 500,
      "budget_type": "per_month",
      "status": "active",
      "created_at": "2025-12-19T11:00:00.000000Z"
    },
    {
      "id": 9,
      "student_id": 1,
      "location": "456 Oak Avenue, Boston",
      "subjects": ["English"],
      "level": "Middle School",
      "budget": 300,
      "status": "closed",
      "created_at": "2025-12-18T09:00:00.000000Z"
    }
  ]
}
```

### Get Single Requirement
```http
GET /student/requirements/10
Authorization: Bearer {token}
```

**Response:**
```json
{
  "requirement": {
    "id": 10,
    "student_id": 1,
    "location": "123 Main Street, New York",
    "lat": 40.7128,
    "lng": -74.0060,
    "phone": "1234567890",
    "details": "Looking for an experienced math tutor...",
    "subjects": ["Mathematics", "Physics"],
    "level": "High School",
    "service_type": "both",
    "meeting_options": ["student_home", "online"],
    "budget": 500,
    "budget_type": "per_month",
    "gender_preference": "no_preference",
    "availability": ["monday", "wednesday", "friday"],
    "time_preference": "Evening (5 PM - 8 PM)",
    "languages": ["English", "Hindi"],
    "tutor_location_preference": "Within 5 km",
    "max_distance": 5,
    "status": "active",
    "created_at": "2025-12-19T11:00:00.000000Z",
    "updated_at": "2025-12-19T11:00:00.000000Z"
  }
}
```

### Update Requirement
```http
PUT /student/requirements/10
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "paused",
  "details": "Updated details: Now looking for weekend classes only.",
  "budget": 600
}
```

**Response:**
```json
{
  "message": "Requirement updated successfully.",
  "requirement": {
    "id": 10,
    "status": "paused",
    "details": "Updated details: Now looking for weekend classes only.",
    "budget": 600,
    "updated_at": "2025-12-19T11:30:00.000000Z"
  }
}
```

### Delete Requirement
```http
DELETE /student/requirements/10
Authorization: Bearer {token}
```

**Response:**
```json
{
  "message": "Requirement deleted successfully."
}
```

---

## 6. Testing Scenarios

### Scenario 1: Complete Student Journey
1. **Register as Student**
   ```
   POST /register (role: student)
   ```
2. **Login**
   ```
   POST /login
   ```
3. **Submit Tutor Request**
   ```
   POST /student/request-tutor (all 12 fields)
   ```
4. **View Requirements**
   ```
   GET /student/requirements
   ```
5. **Enroll as Teacher**
   ```
   POST /user/enroll-teacher
   ```
6. **Verify Dual Role**
   ```
   GET /user (should have both tutor and student)
   ```

### Scenario 2: Complete Teacher Journey
1. **Register as Tutor**
   ```
   POST /register (role: tutor)
   ```
2. **Login**
   ```
   POST /login
   ```
3. **Update Profile**
   ```
   PUT /user/profile
   ```
4. **Upload Photo**
   ```
   POST /user/photo
   ```
5. **Enroll as Student**
   ```
   POST /user/enroll-student
   ```
6. **Submit Tutor Request**
   ```
   POST /student/request-tutor
   ```

### Scenario 3: Profile Management
1. **Update Name/Email**
   ```
   PUT /user/profile
   ```
2. **Upload Avatar**
   ```
   POST /user/photo
   ```
3. **Send Phone OTP**
   ```
   POST /user/phone/send-otp
   ```
4. **Verify Phone**
   ```
   POST /user/phone/verify-otp
   ```

---

## 7. Error Responses

### 400 - Already Enrolled
```json
{
  "message": "You are already enrolled as a teacher."
}
```

### 401 - Unauthenticated
```json
{
  "message": "Unauthenticated."
}
```

### 404 - Not Found
```json
{
  "message": "Requirement not found."
}
```

### 422 - Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "subjects": ["The subjects field is required."]
  }
}
```

---

## 8. Postman Collection

### Import this JSON into Postman:

```json
{
  "info": {
    "name": "Namate24 - Dual Role System",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost/namate24/public/api"
    },
    {
      "key": "token",
      "value": ""
    }
  ],
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Register Student",
          "request": {
            "method": "POST",
            "header": [],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"name\": \"Test Student\",\n  \"email\": \"student@test.com\",\n  \"password\": \"password123\",\n  \"role\": \"student\"\n}",
              "options": {
                "raw": {
                  "language": "json"
                }
              }
            },
            "url": "{{base_url}}/register"
          }
        },
        {
          "name": "Login",
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "var json = pm.response.json();",
                  "pm.collectionVariables.set('token', json.token);"
                ]
              }
            }
          ],
          "request": {
            "method": "POST",
            "header": [],
            "body": {
              "mode": "raw",
              "raw": "{\n  \"email\": \"student@test.com\",\n  \"password\": \"password123\"\n}",
              "options": {
                "raw": {
                  "language": "json"
                }
              }
            },
            "url": "{{base_url}}/login"
          }
        }
      ]
    },
    {
      "name": "Enrollment",
      "item": [
        {
          "name": "Enroll as Teacher",
          "request": {
            "auth": {
              "type": "bearer",
              "bearer": [
                {
                  "key": "token",
                  "value": "{{token}}"
                }
              ]
            },
            "method": "POST",
            "header": [],
            "url": "{{base_url}}/user/enroll-teacher"
          }
        },
        {
          "name": "Enroll as Student",
          "request": {
            "auth": {
              "type": "bearer",
              "bearer": [
                {
                  "key": "token",
                  "value": "{{token}}"
                }
              ]
            },
            "method": "POST",
            "header": [],
            "url": "{{base_url}}/user/enroll-student"
          }
        }
      ]
    }
  ]
}
```

---

## 9. Quick Test Commands (cURL)

### Login and Get Token
```bash
curl -X POST http://localhost/namate24/public/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student@test.com","password":"password123"}'
```

### Get User with Token
```bash
curl -X GET http://localhost/namate24/public/api/user \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Enroll as Teacher
```bash
curl -X POST http://localhost/namate24/public/api/user/enroll-teacher \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Submit Tutor Request
```bash
curl -X POST http://localhost/namate24/public/api/student/request-tutor \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "location":"New York",
    "phone":"1234567890",
    "details":"Need math tutor",
    "subjects":["Math"],
    "level":"High School",
    "service_type":"online",
    "meeting_options":["online"],
    "budget":500,
    "budget_type":"per_month",
    "gender_preference":"no_preference",
    "availability":["monday"],
    "languages":["English"],
    "tutor_location_preference":"Anywhere"
  }'
```

---

## ✅ Testing Checklist

- [ ] Register new student user
- [ ] Login and receive JWT token
- [ ] Get user data with relationships
- [ ] Enroll existing user as teacher
- [ ] Enroll existing user as student
- [ ] Verify dual role (user has both tutor and student)
- [ ] Submit 12-step tutor request
- [ ] Get all requirements
- [ ] Update requirement status
- [ ] Delete requirement
- [ ] Upload profile photo
- [ ] Update profile information
- [ ] Send phone OTP
- [ ] Verify phone OTP

**Status**: Ready for testing! 🚀
