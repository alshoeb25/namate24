# Tutor Profile Management System - Setup Guide

## Overview
This is a comprehensive tutor profile management system for the Namate24 platform. It provides tutors with a complete dashboard to manage all aspects of their professional profile with a step-by-step stepper interface.

## Features

### Profile Sections
1. **Personal Details** - Name, email, phone, gender
2. **Profile Photo** - Professional profile picture upload
3. **Introductory Video** - Video introduction to students
4. **Subjects** - Teaching subjects and expertise levels
5. **Address** - Location information (street, city, state, country, postal code)
6. **Education** - Educational background and qualifications
7. **Experience** - Professional work experience
8. **Teaching Details** - Hourly rate, experience years, teaching modes
9. **Profile Description** - Headline, about, and teaching methodology
10. **Courses** - Custom courses offered
11. **View Profile** - Public profile preview
12. **Settings** - Notifications, visibility, language preferences

## Installation Steps

### 1. Run Migration
```bash
php artisan migrate
```

This will add the following columns to the `tutors` table:
- `address`, `state`, `country`, `postal_code` (Location)
- `introductory_video`, `video_title` (Video content)
- `teaching_methodology` (Teaching approach)
- `educations`, `experiences`, `courses` (JSON arrays)
- `availability` (Teaching schedule)
- `settings` (User preferences)

### 2. Update User Model (if needed)
The User model already supports settings. Ensure it has the `settings` column or add:
```bash
php artisan tinker
```

### 3. File Structure

```
app/
├── Http/Controllers/Tutor/
│   └── ProfileController.php
├── Models/
│   ├── Tutor.php (updated)
│   └── User.php

routes/
├── tutor.php (new)
└── web.php (updated)

resources/views/tutor/profile/
├── dashboard.blade.php
└── steps/
    ├── personal-details.blade.php
    ├── photo.blade.php
    ├── video.blade.php
    ├── subjects.blade.php
    ├── address.blade.php
    ├── education.blade.php
    ├── experience.blade.php
    ├── teaching-details.blade.php
    ├── description.blade.php
    ├── courses.blade.php
    ├── settings.blade.php
    └── view-profile.blade.php

database/migrations/
└── 2024_12_08_000000_add_profile_fields_to_tutors_table.php
```

## Usage

### For Tutors

#### Access Profile Dashboard
Navigate to: `/tutor/profile`

#### Available Routes

| Route | Description |
|-------|-------------|
| `/tutor/profile` | Dashboard overview |
| `/tutor/profile/personal-details` | Edit personal info |
| `/tutor/profile/photo` | Upload profile photo |
| `/tutor/profile/video` | Upload intro video |
| `/tutor/profile/subjects` | Manage subjects |
| `/tutor/profile/address` | Edit location |
| `/tutor/profile/education` | Manage education |
| `/tutor/profile/experience` | Manage experience |
| `/tutor/profile/teaching-details` | Set rates & availability |
| `/tutor/profile/description` | Write profile description |
| `/tutor/profile/courses` | Create/manage courses |
| `/tutor/profile/view/{id?}` | View public profile |
| `/tutor/profile/settings` | Manage preferences |

### Dashboard Features

1. **Profile Completion Percentage**
   - Visual progress bar showing profile completion
   - Calculated based on all sections filled

2. **Quick Navigation Cards**
   - Each section has a card with status and action button
   - Color-coded by section type
   - Shows current value (e.g., "4 subjects selected")

3. **Profile Completion Tracker**
   - Monitors all 10 main sections
   - Helps tutors understand what's needed

## Data Structure

### Education Entry
```json
{
  "degree": "Bachelor of Science",
  "institution": "University Name",
  "field_of_study": "Mathematics",
  "start_year": 2015,
  "end_year": 2018,
  "description": "Optional details"
}
```

### Experience Entry
```json
{
  "title": "Mathematics Teacher",
  "company": "School Name",
  "start_date": "2020-01-15",
  "end_date": "2023-12-31",
  "currently_working": false,
  "description": "Optional details"
}
```

### Course Entry
```json
{
  "title": "Algebra Mastery",
  "description": "Course description",
  "duration": "8 weeks",
  "level": "beginner",
  "price": 99.99
}
```

### Settings
```json
{
  "email_notifications": true,
  "sms_notifications": false,
  "profile_visibility": "public",
  "language": "en"
}
```

## Validation Rules

### Personal Details
- Name: Required, max 255 chars
- Email: Required, email format, unique
- Phone: Required, max 20 chars
- Gender: Required, one of: male, female, other

### Photo
- File: Required, image, JPEG/PNG/GIF, max 2MB

### Video
- File: Required, video, MP4/MOV/AVI/WMV, max 100MB
- Title: Required, max 255 chars

### Teaching Details
- Experience Years: Integer, 0-70
- Price Per Hour: Numeric, min 0
- Teaching Mode: Array, at least one selected
- Availability: Required, text description

### Profile Description
- Headline: Required, max 255 chars
- About: Required, 50-2000 chars
- Teaching Methodology: Required, max 1000 chars

## Profile Completion Calculation

The system tracks completion of:
1. Personal Details (name, email, phone)
2. Photo (avatar uploaded)
3. Video (introductory video uploaded)
4. Subjects (at least 1 subject selected)
5. Address (city set)
6. Education (at least 1 entry)
7. Experience (at least 1 entry)
8. Teaching Details (rate & experience set)
9. Description (headline & about filled)
10. Courses (at least 1 course created)

Score = (Completed Sections / 10) × 100%

## Middleware

All profile routes require:
- `auth` - User must be authenticated
- `role:tutor` - User must have tutor role

## Styling

The system uses Tailwind CSS with:
- Color-coded sections (different colors for each profile area)
- Responsive grid layouts
- Card-based UI design
- Progress indicators
- Form validation feedback

## File Uploads

### Photo Storage
- Location: `storage/app/public/avatars/`
- Accessible at: `/storage/avatars/filename`

### Video Storage
- Location: `storage/app/public/videos/introductory/`
- Accessible at: `/storage/videos/introductory/filename`

## Security Notes

1. All routes are protected with authentication
2. Tutor role middleware ensures only tutors can access
3. File uploads are validated for type and size
4. CSRF protection on all forms
5. SQL injection prevention through Eloquent ORM

## Customization

### Modify Profile Completion Calculation
Edit `calculateProfileCompletion()` method in `ProfileController.php`

### Add New Profile Section
1. Create a new view in `resources/views/tutor/profile/steps/`
2. Add route in `routes/tutor.php`
3. Add controller methods (view & update)
4. Update migration if needed
5. Update `calculateProfileCompletion()` method

### Change Color Scheme
Update the Tailwind color classes in the blade templates.

## Troubleshooting

### Routes Not Working
- Ensure `tutor.php` is included in `web.php`
- Check that routes are in the correct group
- Verify middleware setup

### File Uploads Not Working
- Ensure storage is linked: `php artisan storage:link`
- Check file permissions on storage directory
- Verify disk configuration in `config/filesystems.php`

### Profile Completion Not Showing
- Clear view cache: `php artisan view:clear`
- Verify Tutor model relationships
- Check database migration ran successfully

## Support

For issues or questions, please contact the development team.

---

**Created:** December 8, 2024
**Laravel Version:** 11.x
**PHP Version:** 8.2+
