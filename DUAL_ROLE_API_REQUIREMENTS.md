# Dual Role User System - API Requirements

## Overview
The system uses a single user account that can have both tutor and student roles through separate table relationships.

## Database Structure

### Users Table (Main)
```sql
users
- id (PK)
- name
- email (unique)
- phone (unique, nullable)
- email_verified_at
- phone_verified_at
- password
- avatar (profile picture path)
- remember_token
- timestamps
```

### Tutors Table (One-to-One with Users)
```sql
tutors
- id (PK)
- user_id (FK to users.id, unique)
- photo_url
- video_url
- bio/description
- hourly_rate
- teaching_mode
- subjects (JSON or separate pivot table)
- education (JSON or separate table)
- experience (JSON or separate table)
- address details
- timestamps
```

### Students Table (One-to-One with Users)
```sql
students
- id (PK)
- user_id (FK to users.id, unique)
- grade_level
- subjects_interested (JSON)
- parent_name (if registering as parent)
- timestamps
```

### Tutor Requirements Table (Student Requests)
```sql
tutor_requirements
- id (PK)
- student_id (FK to students.id)
- city
- area
- pincode
- phone
- alternate_phone
- student_name
- description
- subjects (JSON)
- level
- service_type (tutoring, assignment_help)
- meeting_options (JSON: online, at_my_place, travel_to_tutor)
- travel_distance
- budget_amount
- budget_type (fixed, per_hour, per_day, per_week, per_month, per_year)
- gender_preference
- availability (part_time, full_time)
- languages (JSON)
- tutor_location (all_countries, india_only)
- status (active, fulfilled, closed)
- timestamps
```

## Required API Endpoints

### 1. Authentication & Registration

#### POST /api/register
**Request:**
```json
{
  "name": "John Doe",
  "identifier": "john@example.com OR +919876543210",
  "password": "password123",
  "confirmPassword": "password123",
  "role": "student" OR "tutor"
}
```
**Response:**
```json
{
  "token": "api_token_here",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+919876543210",
    "avatar": null,
    "tutor": null,  // or tutor object if role is tutor
    "student": {    // or null if role is tutor
      "id": 1,
      "user_id": 1,
      "grade_level": null,
      ...
    }
  }
}
```

**Note:** When registering:
- If role is "tutor", create user + tutor record
- If role is "student", create user + student record
- Return user object with related tutor/student loaded

---

### 2. User Management

#### GET /api/user
Get authenticated user with relationships
**Response:**
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+919876543210",
  "email_verified_at": "2024-01-01 00:00:00",
  "phone_verified_at": "2024-01-01 00:00:00",
  "avatar": "avatars/user1.jpg",
  "tutor": {
    "id": 1,
    "user_id": 1,
    "photo_url": "photos/tutor1.jpg",
    "hourly_rate": 500,
    ...
  },
  "student": {
    "id": 1,
    "user_id": 1,
    "grade_level": "undergraduate",
    ...
  }
}
```

#### POST /api/user/enroll-teacher
Enroll existing user as teacher (creates tutor record)
**Request:** None (uses authenticated user)
**Response:**
```json
{
  "message": "Successfully enrolled as teacher",
  "tutor": {
    "id": 1,
    "user_id": 1,
    ...
  }
}
```

#### POST /api/user/enroll-student
Enroll existing user as student (creates student record)
**Request:** None (uses authenticated user)
**Response:**
```json
{
  "message": "Successfully enrolled as student",
  "student": {
    "id": 1,
    "user_id": 1,
    ...
  }
}
```

---

### 3. Profile Management

#### PUT /api/profile
Update user profile (name, email, phone)
**Request:**
```json
{
  "name": "John Doe Updated",
  "email": "newemail@example.com",
  "phone": "+919876543210"
}
```
**Response:**
```json
{
  "message": "Profile updated successfully",
  "user": { ... }
}
```

#### POST /api/profile/photo
Upload profile photo
**Request:** multipart/form-data with 'photo' file
**Response:**
```json
{
  "message": "Photo uploaded successfully",
  "avatar": "avatars/user1.jpg"
}
```

#### POST /api/profile/email/verification
Send email verification link
**Request:**
```json
{
  "email": "newemail@example.com"
}
```
**Response:**
```json
{
  "message": "Verification email sent"
}
```

#### POST /api/profile/phone/otp
Send OTP to phone
**Request:**
```json
{
  "phone": "+919876543210"
}
```
**Response:**
```json
{
  "message": "OTP sent successfully"
}
```

#### POST /api/profile/phone/verify
Verify phone with OTP
**Request:**
```json
{
  "phone": "+919876543210",
  "otp": "123456"
}
```
**Response:**
```json
{
  "message": "Phone verified successfully",
  "phone_verified_at": "2024-01-01 00:00:00"
}
```

---

### 4. Student - Request Tutor

#### POST /api/student/request-tutor
Create a tutor requirement request
**Request:**
```json
{
  "city": "Mumbai",
  "area": "Andheri",
  "pincode": "400053",
  "phone": "+919876543210",
  "alternate_phone": "+919876543211",
  "student_name": "Rahul Sharma",
  "description": "Need help with Mathematics and Physics",
  "subjects": ["Mathematics", "Physics"],
  "other_subject": "",
  "level": "Class 11-12",
  "service_type": "tutoring",
  "meeting_options": ["online", "at_my_place"],
  "travel_distance": null,
  "budget_amount": 5000,
  "budget_type": "per_month",
  "gender_preference": "no_preference",
  "availability": "part_time",
  "languages": ["English", "Hindi"],
  "tutor_location": "india_only"
}
```
**Response:**
```json
{
  "message": "Request submitted successfully",
  "requirement": {
    "id": 1,
    "student_id": 1,
    "city": "Mumbai",
    "subjects": ["Mathematics", "Physics"],
    "status": "active",
    ...
  }
}
```

#### GET /api/student/requirements
Get all requirements for authenticated student
**Response:**
```json
[
  {
    "id": 1,
    "subjects": ["Mathematics", "Physics"],
    "level": "Class 11-12",
    "city": "Mumbai",
    "description": "Need help with Mathematics and Physics",
    "budget": "₹5000/month",
    "availability": "Part Time",
    "status": "active",
    "created_at": "2024-01-01"
  }
]
```

---

### 5. Tutor Profile Management

All existing tutor profile endpoints remain the same:
- POST /api/tutor/profile/personal-details
- POST /api/tutor/profile/photo
- POST /api/tutor/profile/video
- POST /api/tutor/profile/subjects
- POST /api/tutor/profile/address
- POST /api/tutor/profile/education
- POST /api/tutor/profile/experience
- etc.

**Important:** These endpoints should check `user.tutor` relationship exists, not `user.role`

---

## Migration Strategy

If migrating from role-based to relationship-based:

1. Keep existing `users` table
2. Create `tutors` table with `user_id` foreign key
3. Create `students` table with `user_id` foreign key
4. For existing users with `role='tutor'`, create tutor records
5. For existing users with `role='student'`, create student records
6. Remove or deprecate `role` column from users table

## Frontend Usage

The frontend now expects user object with relationships:
```javascript
user: {
  id: 1,
  name: "John Doe",
  email: "john@example.com",
  tutor: { ... },    // null if not a tutor
  student: { ... }   // null if not a student
}
```

Checking roles:
```javascript
// Check if user is a tutor
if (user.tutor) { ... }

// Check if user is a student
if (user.student) { ... }

// User can be both
if (user.tutor && user.student) { ... }
```

## Security Considerations

1. Protect tutor-only routes by checking `user.tutor` exists
2. Protect student-only routes by checking `user.student` exists
3. Validate enrollment requests (one tutor/student record per user)
4. Ensure proper authorization for profile updates
5. Rate limit OTP requests
6. Validate email verification tokens
