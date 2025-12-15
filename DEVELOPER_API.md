# Tutor Profile System - Developer API Documentation

## Table of Contents
1. [Controller Methods](#controller-methods)
2. [Data Models](#data-models)
3. [Validation Rules](#validation-rules)
4. [Response Formats](#response-formats)
5. [Error Handling](#error-handling)
6. [Usage Examples](#usage-examples)

---

## Controller Methods

### ProfileController Class
**Location:** `app/Http/Controllers/Tutor/ProfileController.php`

#### Constructor
```php
public function __construct()
{
    $this->middleware('auth');
    $this->middleware('role:tutor');
}
```
Ensures all methods require authentication and tutor role.

---

## Dashboard Methods

### dashboard()
**Route:** `GET /tutor/profile`

Displays the profile dashboard with all sections and completion percentage.

**Returns:**
- View: `tutor.profile.dashboard`
- Data:
  - `tutor` - Current user's Tutor model
  - `completionPercentage` - Integer 0-100

**Access:** Tutor only

---

## Personal Details Methods

### personalDetails()
**Route:** `GET /tutor/profile/personal-details`

Shows form to edit personal information.

**Returns:**
- View: `tutor.profile.steps.personal-details`
- Data:
  - `tutor` - Current user's Tutor model
  - `user` - Current authenticated User

### updatePersonalDetails(Request $request)
**Route:** `POST /tutor/profile/personal-details`

Updates personal information.

**Validation:**
```php
[
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email,' . $user->id,
    'phone' => 'required|string|max:20',
    'gender' => 'required|in:male,female,other',
]
```

**Updates:**
- `users` table: name, email, phone
- `tutors` table: gender

**Response:** Redirect to dashboard with success message

---

## Photo Methods

### photo()
**Route:** `GET /tutor/profile/photo`

Shows photo upload form.

**Returns:**
- View: `tutor.profile.steps.photo`
- Data: `tutor`

### updatePhoto(Request $request)
**Route:** `POST /tutor/profile/photo`

Uploads and saves profile photo.

**Validation:**
```php
[
    'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
]
```

**Storage:**
- Old avatar deleted if exists
- New file stored in: `storage/public/avatars/`
- Updates `users.avatar` with path

**Response:** Redirect to dashboard with success message

---

## Video Methods

### video()
**Route:** `GET /tutor/profile/video`

Shows video upload form.

**Returns:**
- View: `tutor.profile.steps.video`
- Data: `tutor`

### updateVideo(Request $request)
**Route:** `POST /tutor/profile/video`

Uploads and saves introductory video.

**Validation:**
```php
[
    'video' => 'required|mimes:mp4,mov,avi,wmv|max:102400',
    'video_title' => 'required|string|max:255',
]
```

**Storage:**
- Old video deleted if exists
- New file stored in: `storage/public/videos/introductory/`
- Updates tutor: `introductory_video`, `video_title`

**Response:** Redirect to dashboard with success message

---

## Subjects Methods

### subjects()
**Route:** `GET /tutor/profile/subjects`

Shows subject selection form with expertise levels.

**Returns:**
- View: `tutor.profile.steps.subjects`
- Data:
  - `tutor` - Current tutor
  - `allSubjects` - All available subjects
  - `selectedSubjects` - Currently selected subjects with levels

### updateSubjects(Request $request)
**Route:** `POST /tutor/profile/update-subjects`

Updates selected subjects and expertise levels.

**Validation:**
```php
[
    'subjects' => 'required|array|min:1',
    'subjects.*.id' => 'required|exists:subjects,id',
    'subjects.*.level' => 'required|in:beginner,intermediate,advanced',
]
```

**Updates:**
- `tutor_subject` pivot table with subject IDs and levels

**Response:** Redirect to dashboard with success message

---

## Address Methods

### address()
**Route:** `GET /tutor/profile/address`

Shows address form with map coordinates.

**Returns:**
- View: `tutor.profile.steps.address`
- Data: `tutor`

### updateAddress(Request $request)
**Route:** `POST /tutor/profile/update-address`

Updates location information.

**Validation:**
```php
[
    'address' => 'required|string|max:255',
    'city' => 'required|string|max:100',
    'state' => 'required|string|max:100',
    'country' => 'required|string|max:100',
    'postal_code' => 'required|string|max:20',
    'lat' => 'required|numeric',
    'lng' => 'required|numeric',
]
```

**Updates:** Tutor model with all address fields

**Response:** Redirect to dashboard with success message

---

## Education Methods

### education()
**Route:** `GET /tutor/profile/education`

Shows list of education entries with add form.

**Returns:**
- View: `tutor.profile.steps.education`
- Data:
  - `tutor`
  - `educations` - Array of education entries

### storeEducation(Request $request)
**Route:** `POST /tutor/profile/store-education`

Adds new education entry.

**Validation:**
```php
[
    'degree' => 'required|string|max:255',
    'institution' => 'required|string|max:255',
    'field_of_study' => 'required|string|max:255',
    'start_year' => 'required|integer|min:1950|max:' . date('Y'),
    'end_year' => 'nullable|integer|min:1950|max:' . (date('Y') + 5),
    'description' => 'nullable|string|max:1000',
]
```

**Data Structure:**
```php
[
    'degree' => 'Bachelor of Science',
    'institution' => 'University Name',
    'field_of_study' => 'Mathematics',
    'start_year' => 2015,
    'end_year' => 2018,
    'description' => 'Additional info'
]
```

**Updates:** Tutor's `educations` JSON array

**Response:** Redirect to education page with success message

### updateEducation(Request $request, $index)
**Route:** `POST /tutor/profile/education/{index}`

Updates existing education entry by index.

**Parameters:**
- `$index` - Array index of education entry

**Same validation as storeEducation**

**Response:** Redirect to education page with success message

### deleteEducation($index)
**Route:** `DELETE /tutor/profile/education/{index}`

Deletes education entry by index.

**Response:** Redirect to education page with success message

---

## Experience Methods

### experience()
**Route:** `GET /tutor/profile/experience`

Shows list of experience entries with add form.

**Returns:**
- View: `tutor.profile.steps.experience`
- Data:
  - `tutor`
  - `experiences` - Array of experience entries

### storeExperience(Request $request)
**Route:** `POST /tutor/profile/store-experience`

Adds new experience entry.

**Validation:**
```php
[
    'title' => 'required|string|max:255',
    'company' => 'required|string|max:255',
    'start_date' => 'required|date',
    'end_date' => 'nullable|date|after:start_date',
    'currently_working' => 'boolean',
    'description' => 'nullable|string|max:1000',
]
```

**Data Structure:**
```php
[
    'title' => 'Mathematics Teacher',
    'company' => 'School Name',
    'start_date' => '2020-01-15',
    'end_date' => '2023-12-31',
    'currently_working' => false,
    'description' => 'Job description'
]
```

**Updates:** Tutor's `experiences` JSON array

**Response:** Redirect to experience page with success message

### updateExperience(Request $request, $index)
**Route:** `POST /tutor/profile/experience/{index}`

Updates existing experience entry by index.

**Parameters:**
- `$index` - Array index of experience entry

**Same validation as storeExperience**

### deleteExperience($index)
**Route:** `DELETE /tutor/profile/experience/{index}`

Deletes experience entry by index.

---

## Teaching Details Methods

### teachingDetails()
**Route:** `GET /tutor/profile/teaching-details`

Shows teaching details form.

**Returns:**
- View: `tutor.profile.steps.teaching-details`
- Data: `tutor`

### updateTeachingDetails(Request $request)
**Route:** `POST /tutor/profile/update-teaching-details`

Updates teaching details.

**Validation:**
```php
[
    'experience_years' => 'required|integer|min:0|max:70',
    'price_per_hour' => 'required|numeric|min:0',
    'teaching_mode' => 'required|array',
    'teaching_mode.*' => 'in:online,offline,both',
    'availability' => 'required|string',
]
```

**Updates:** Tutor model

**Response:** Redirect to dashboard with success message

---

## Description Methods

### description()
**Route:** `GET /tutor/profile/description`

Shows profile description form with preview.

**Returns:**
- View: `tutor.profile.steps.description`
- Data: `tutor`

### updateDescription(Request $request)
**Route:** `POST /tutor/profile/update-description`

Updates profile description.

**Validation:**
```php
[
    'headline' => 'required|string|max:255',
    'about' => 'required|string|min:50|max:2000',
    'teaching_methodology' => 'required|string|max:1000',
]
```

**Updates:** Tutor model

**Response:** Redirect to dashboard with success message

---

## Courses Methods

### courses()
**Route:** `GET /tutor/profile/courses`

Shows list of courses with create form.

**Returns:**
- View: `tutor.profile.steps.courses`
- Data:
  - `tutor`
  - `courses` - Array of course entries

### storeCourse(Request $request)
**Route:** `POST /tutor/profile/store-course`

Adds new course.

**Validation:**
```php
[
    'title' => 'required|string|max:255',
    'description' => 'required|string|max:1000',
    'duration' => 'required|string|max:100',
    'level' => 'required|in:beginner,intermediate,advanced',
    'price' => 'required|numeric|min:0',
]
```

**Data Structure:**
```php
[
    'title' => 'Algebra Mastery',
    'description' => 'Learn algebra basics',
    'duration' => '8 weeks',
    'level' => 'beginner',
    'price' => 99.99
]
```

**Updates:** Tutor's `courses` JSON array

**Response:** Redirect to courses page with success message

### updateCourse(Request $request, $index)
**Route:** `POST /tutor/profile/courses/{index}`

Updates existing course by index.

**Parameters:**
- `$index` - Array index of course

**Same validation as storeCourse**

### deleteCourse($index)
**Route:** `DELETE /tutor/profile/courses/{index}`

Deletes course by index.

---

## Profile View Methods

### viewProfile($id = null)
**Route:** `GET /tutor/profile/view/{id?}`

Shows public profile view.

**Parameters:**
- `$id` - (optional) Tutor ID. If not provided, shows current user's profile

**Returns:**
- View: `tutor.profile.view-profile`
- Data: `tutor` - The Tutor model with relationships loaded

**Access:** Public (if profile visibility is public)

---

## Settings Methods

### settings()
**Route:** `GET /tutor/profile/settings`

Shows settings form.

**Returns:**
- View: `tutor.profile.steps.settings`
- Data: `user` - Current authenticated User

### updateSettings(Request $request)
**Route:** `POST /tutor/profile/update-settings`

Updates user settings.

**Validation:**
```php
[
    'email_notifications' => 'boolean',
    'sms_notifications' => 'boolean',
    'profile_visibility' => 'required|in:public,private',
    'language' => 'required|in:en,es,fr,de',
]
```

**Data Structure:**
```php
[
    'email_notifications' => true,
    'sms_notifications' => false,
    'profile_visibility' => 'public',
    'language' => 'en'
]
```

**Updates:** User model's `settings` JSON field

**Response:** Redirect to settings with success message

---

## Helper Methods

### calculateProfileCompletion($tutor)
**Visibility:** Private

Calculates profile completion percentage.

**Logic:**
Checks 10 sections for completion:
1. Personal details (user name, email, phone)
2. Photo (avatar exists)
3. Video (introductory video exists)
4. Subjects (at least 1 selected)
5. Address (city set)
6. Education (at least 1 entry)
7. Experience (at least 1 entry)
8. Teaching details (rates set)
9. Description (headline & about filled)
10. Courses (at least 1 course)

**Returns:** Integer 0-100

**Formula:** `(completed_sections / 10) * 100`

---

## Data Models

### Tutor Model

**Relationships:**
```php
public function user(): BelongsTo
public function subjects(): BelongsToMany
```

**Fillable Fields:**
```php
'user_id', 'headline', 'about', 'experience_years', 'price_per_hour',
'teaching_mode', 'city', 'lat', 'lng', 'verified', 'rating_avg', 'rating_count',
'gender', 'badges', 'moderation_status', 'address', 'state', 'country', 
'postal_code', 'introductory_video', 'video_title', 'teaching_methodology',
'educations', 'experiences', 'courses', 'availability', 'settings'
```

**Casts:**
```php
'verified' => 'boolean',
'badges' => 'array',
'teaching_mode' => 'array',
'educations' => 'array',
'experiences' => 'array',
'courses' => 'array',
'settings' => 'array',
```

---

## Validation Rules

### Summary Table

| Section | Field | Type | Rules |
|---------|-------|------|-------|
| Personal | name | string | required, max:255 |
| Personal | email | email | required, unique, email |
| Personal | phone | string | required, max:20 |
| Personal | gender | select | required, in:male,female,other |
| Photo | file | image | required, max:2048 |
| Video | file | video | required, max:102400 |
| Video | title | string | required, max:255 |
| Address | address | string | required, max:255 |
| Address | city | string | required, max:100 |
| Address | lat/lng | numeric | required, numeric |
| Teaching | rate | numeric | required, min:0 |
| Teaching | years | integer | required, 0-70 |
| Description | headline | string | required, max:255 |
| Description | about | text | required, 50-2000 chars |

---

## Response Formats

### Success Response (Redirect)
```php
return redirect()->route('route.name')
    ->with('success', 'Message displayed to user');
```

### Error Response (Validation)
```php
Automatically handled by Laravel validation
Errors displayed in view with error messages
```

### View Response
```php
return view('view.path', [
    'data_key' => $data_value,
    'another_key' => $another_value,
]);
```

---

## Error Handling

### Validation Errors
- Automatically caught and displayed in forms
- Old input preserved for user convenience
- Error messages shown above each field

### File Upload Errors
- Size limit exceeded
- Invalid file type
- Storage errors

### Database Errors
- Transaction failures handled gracefully
- Relationship errors managed
- Constraint violations prevented

### Access Errors
- Unauthenticated users redirected to login
- Non-tutors denied access
- 403 Forbidden errors returned

---

## Usage Examples

### Example 1: Update Personal Details

**Request:**
```
POST /tutor/profile/personal-details
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "gender": "male"
}
```

**Response:**
- Redirect to `/tutor/profile` 
- Success message: "Personal details updated successfully!"

### Example 2: Upload Profile Photo

**Request:**
```
POST /tutor/profile/photo
Form data:
- photo: [image file]
```

**Response:**
- File saved to: `storage/public/avatars/filename.ext`
- Database updated with path
- Redirect to dashboard

### Example 3: Add Education

**Request:**
```
POST /tutor/profile/store-education
{
    "degree": "Master of Arts",
    "institution": "Harvard University",
    "field_of_study": "Mathematics",
    "start_year": 2018,
    "end_year": 2020,
    "description": "Focused on theoretical mathematics"
}
```

**Response:**
- Added to `tutors.educations` JSON array
- Redirect to education page
- New entry displays in list

### Example 4: Add Multiple Subjects

**Request:**
```
POST /tutor/profile/update-subjects
{
    "subjects": [
        {"id": 1, "level": "advanced"},
        {"id": 2, "level": "intermediate"},
        {"id": 3, "level": "beginner"}
    ]
}
```

**Response:**
- Synced with pivot table
- Subject count updated
- Completion percentage recalculated

---

## Performance Considerations

### Query Optimization
- Use eager loading: `with('subjects')`
- Avoid N+1 queries
- Cache where possible

### File Optimization
- Limit upload sizes
- Compress images
- Clean up old files

### Database
- Index frequently queried fields
- Normalize data where possible
- Archive old records

---

## Security Best Practices

1. **Authentication**
   - Always verify user is logged in
   - Check user roles/permissions

2. **Authorization**
   - Only tutor role can access routes
   - Users can only edit their own profile

3. **File Uploads**
   - Validate file type
   - Validate file size
   - Store outside web root when possible

4. **Input Validation**
   - Validate all inputs
   - Sanitize user data
   - Use parameterized queries

5. **CSRF Protection**
   - Use @csrf in all forms
   - Verify token in requests

---

**Version:** 1.0
**Last Updated:** December 8, 2024
**For Questions:** Contact Development Team
