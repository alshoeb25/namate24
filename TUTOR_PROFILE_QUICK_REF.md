# Tutor Profile System - Quick Reference Guide

## Dashboard Overview

The tutor profile system provides a comprehensive dashboard where tutors can manage all aspects of their professional profile. The system tracks progress and shows a completion percentage.

## Profile Sections at a Glance

### 1. Personal Details
- **Route:** `/tutor/profile/personal-details`
- **Fields:** Name, Email, Phone, Gender
- **Required:** Yes
- **Storage:** Users table

### 2. Profile Photo
- **Route:** `/tutor/profile/photo`
- **Requirements:** JPEG/PNG/GIF, max 2MB
- **Storage:** `storage/public/avatars/`
- **Required for completion:** Yes

### 3. Introductory Video
- **Route:** `/tutor/profile/video`
- **Requirements:** MP4/MOV/AVI/WMV, max 100MB
- **Storage:** `storage/public/videos/introductory/`
- **Required for completion:** Yes

### 4. Subjects
- **Route:** `/tutor/profile/subjects`
- **Fields:** Subject selection + expertise level
- **Levels:** Beginner, Intermediate, Advanced
- **Required:** At least 1 subject
- **Storage:** tutors_subject pivot table with level

### 5. Address
- **Route:** `/tutor/profile/address`
- **Fields:** Street, City, State, Country, Postal Code, Lat, Lng
- **Required:** Yes
- **Storage:** Tutors table

### 6. Education
- **Route:** `/tutor/profile/education`
- **Fields per entry:** Degree, Institution, Field, Start Year, End Year, Description
- **Required:** At least 1 entry
- **Storage:** JSON array in tutors table

### 7. Experience
- **Route:** `/tutor/profile/experience`
- **Fields per entry:** Title, Company, Start Date, End Date, Currently Working, Description
- **Required:** At least 1 entry
- **Storage:** JSON array in tutors table

### 8. Teaching Details
- **Route:** `/tutor/profile/teaching-details`
- **Fields:** Experience Years, Price/Hour, Teaching Mode, Availability
- **Teaching Modes:** Online, Offline, Both
- **Required:** Yes
- **Storage:** Tutors table

### 9. Profile Description
- **Route:** `/tutor/profile/description`
- **Fields:** Headline (max 255), About (50-2000), Teaching Methodology (max 1000)
- **Required:** Yes
- **Storage:** Tutors table

### 10. Courses
- **Route:** `/tutor/profile/courses`
- **Fields per entry:** Title, Description, Duration, Level, Price
- **Levels:** Beginner, Intermediate, Advanced
- **Storage:** JSON array in tutors table

### 11. View Profile
- **Route:** `/tutor/profile/view/{id?}` or `/tutor/profile/view`
- **Purpose:** Preview public profile
- **Visibility:** Depends on settings

### 12. Settings
- **Route:** `/tutor/profile/settings`
- **Options:** Email notifications, SMS notifications, Profile visibility, Language
- **Storage:** JSON in users table

## Form Methods

```php
// GET - Display form
GET /tutor/profile/{section}

// POST - Save data
POST /tutor/profile/update-{section}

// DELETE - Remove entry (for collections)
DELETE /tutor/profile/{section}/{index}
```

## Profile Completion Weights

Each section counts toward profile completion percentage:

```
Personal Details: 10%
Photo: 10%
Video: 10%
Subjects: 10%
Address: 10%
Education: 10%
Experience: 10%
Teaching Details: 10%
Description: 10%
Courses: 10%
```

**Total: 100% when all sections complete**

## Navigation Flow

```
Dashboard (/) 
    ├── Personal Details
    ├── Photo
    ├── Video
    ├── Subjects
    ├── Address
    ├── Education
    ├── Experience
    ├── Teaching Details
    ├── Description
    ├── Courses
    ├── View Profile
    └── Settings
```

## Key Features

### Progress Tracking
- Visual progress bar on dashboard
- Percentage complete display
- Section completion indicators

### Smart Validation
- Real-time form validation
- Character count tracking (Description)
- File type and size validation
- Email uniqueness check

### User Experience
- Color-coded section cards
- Icon identification for each section
- Quick action buttons
- Modal forms with preview

### Data Management
- Easy add/edit/delete for collections
- JSON storage for flexible data
- Relationship management with subjects

## Common Routes

| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/tutor/profile` | Dashboard |
| GET | `/tutor/profile/personal-details` | Show form |
| POST | `/tutor/profile/personal-details` | Save data |
| GET | `/tutor/profile/photo` | Show upload |
| POST | `/tutor/profile/photo` | Upload file |
| GET | `/tutor/profile/video` | Show upload |
| POST | `/tutor/profile/video` | Upload file |
| GET | `/tutor/profile/subjects` | Show selection |
| POST | `/tutor/profile/update-subjects` | Save subjects |
| GET | `/tutor/profile/address` | Show form |
| POST | `/tutor/profile/update-address` | Save address |
| GET | `/tutor/profile/education` | List educations |
| POST | `/tutor/profile/store-education` | Add education |
| DELETE | `/tutor/profile/education/{id}` | Remove education |
| GET | `/tutor/profile/experience` | List experiences |
| POST | `/tutor/profile/store-experience` | Add experience |
| DELETE | `/tutor/profile/experience/{id}` | Remove experience |
| GET | `/tutor/profile/teaching-details` | Show form |
| POST | `/tutor/profile/update-teaching-details` | Save details |
| GET | `/tutor/profile/description` | Show form |
| POST | `/tutor/profile/update-description` | Save description |
| GET | `/tutor/profile/courses` | List courses |
| POST | `/tutor/profile/store-course` | Add course |
| DELETE | `/tutor/profile/courses/{id}` | Remove course |
| GET | `/tutor/profile/view/{id}` | View public profile |
| GET | `/tutor/profile/settings` | Show settings |
| POST | `/tutor/profile/update-settings` | Save settings |

## Database Fields Added

### Tutors Table Columns
```
address (string)
state (string)
country (string)
postal_code (string)
introductory_video (string)
video_title (string)
teaching_methodology (text)
educations (json)
experiences (json)
courses (json)
availability (text)
settings (json)
```

### Existing Fields Used
```
user_id
headline
about
experience_years
price_per_hour
teaching_mode (converted to array)
city
lat
lng
gender
verified
rating_avg
rating_count
```

## Blade Template Structure

Each view includes:
1. **Back navigation link**
2. **Error display** (if validation fails)
3. **Form with labeled fields**
4. **Validation messages**
5. **Character/count indicators**
6. **Preview components** (where applicable)
7. **Save and Cancel buttons**

## Styling Classes Used

- **Gradients:** `from-[color]-600 to-[color]-700`
- **Cards:** `bg-white rounded-lg shadow-md p-6`
- **Buttons:** `px-6 py-3 bg-[color]-600 text-white rounded-lg hover:bg-[color]-700`
- **Inputs:** `w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[color]-500`
- **Success:** `bg-green-100 border-green-400 text-green-700`
- **Error:** `bg-red-100 border-red-400 text-red-700`

## Middleware Protection

All routes protected by:
```php
middleware(['auth', 'role:tutor'])
```

This ensures:
- User must be logged in
- User must have 'tutor' role
- Only tutors can access profile pages

## File Upload Destinations

| File Type | Max Size | Location | Accessible Via |
|-----------|----------|----------|-----------------|
| Avatar | 2 MB | storage/public/avatars/ | /storage/avatars/{filename} |
| Video | 100 MB | storage/public/videos/introductory/ | /storage/videos/introductory/{filename} |

## Testing URLs (Local Development)

```
http://localhost/tutor/profile
http://localhost/tutor/profile/personal-details
http://localhost/tutor/profile/photo
http://localhost/tutor/profile/video
http://localhost/tutor/profile/subjects
http://localhost/tutor/profile/address
http://localhost/tutor/profile/education
http://localhost/tutor/profile/experience
http://localhost/tutor/profile/teaching-details
http://localhost/tutor/profile/description
http://localhost/tutor/profile/courses
http://localhost/tutor/profile/view
http://localhost/tutor/profile/settings
```

## Troubleshooting Guide

| Issue | Solution |
|-------|----------|
| Routes return 404 | Check tutor.php is included in web.php |
| Files not uploading | Run `php artisan storage:link` |
| Redirects to login | Ensure user has 'tutor' role |
| Views not loading | Clear cache: `php artisan view:clear` |
| Permissions denied | Check storage directory permissions |
| Database error | Run migration: `php artisan migrate` |

## Future Enhancement Ideas

1. **Progress notifications** - Email when profile reaches milestones
2. **Profile reviews** - Admin verification system
3. **Achievement badges** - Reward complete profiles
4. **Analytics** - Track profile views and inquiries
5. **Bulk edit** - Update multiple sections at once
6. **Template profiles** - Pre-filled examples
7. **Social sharing** - Share profile links
8. **Calendar integration** - Sync availability with calendar
9. **Automated scheduling** - Integration with booking system
10. **AI suggestions** - Content recommendations

---

**Version:** 1.0
**Last Updated:** December 8, 2024
**Maintained by:** Development Team
