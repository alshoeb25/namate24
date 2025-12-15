# Tutor Profile System - Quick Setup Checklist

## Pre-Setup Requirements
- [ ] Laravel 11.x installed
- [ ] Database connected and running
- [ ] Authentication system configured
- [ ] User roles/permissions system configured (Spatie/Laravel-permission)
- [ ] Tailwind CSS configured

## Installation Steps

### Step 1: Database Migration
```bash
# Run the migration to add new columns to tutors table
php artisan migrate
```
- [ ] Migration completed without errors
- [ ] Tutor model updated with new fillable fields
- [ ] Database columns verified

### Step 2: Storage Configuration
```bash
# Link storage for file uploads
php artisan storage:link
```
- [ ] Storage link created
- [ ] Storage directory permissions set correctly

### Step 3: Clear Cache
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```
- [ ] All caches cleared

### Step 4: User Role Assignment
Ensure tutors have the 'tutor' role:
```bash
php artisan tinker

# Inside tinker
$user = User::find(1);
$user->assignRole('tutor');
```
- [ ] Tutor role assigned to test users

## File Verification

### Controllers
- [ ] `app/Http/Controllers/Tutor/ProfileController.php` exists and contains all methods

### Models
- [ ] `app/Models/Tutor.php` updated with new fillable fields
- [ ] `app/Models/Tutor.php` updated with new casts

### Routes
- [ ] `routes/tutor.php` created with all profile routes
- [ ] `routes/web.php` updated to include tutor routes

### Views
- [ ] `resources/views/tutor/profile/dashboard.blade.php`
- [ ] `resources/views/tutor/profile/steps/personal-details.blade.php`
- [ ] `resources/views/tutor/profile/steps/photo.blade.php`
- [ ] `resources/views/tutor/profile/steps/video.blade.php`
- [ ] `resources/views/tutor/profile/steps/subjects.blade.php`
- [ ] `resources/views/tutor/profile/steps/address.blade.php`
- [ ] `resources/views/tutor/profile/steps/education.blade.php`
- [ ] `resources/views/tutor/profile/steps/experience.blade.php`
- [ ] `resources/views/tutor/profile/steps/teaching-details.blade.php`
- [ ] `resources/views/tutor/profile/steps/description.blade.php`
- [ ] `resources/views/tutor/profile/steps/courses.blade.php`
- [ ] `resources/views/tutor/profile/steps/settings.blade.php`
- [ ] `resources/views/tutor/profile/view-profile.blade.php`

### Migrations
- [ ] `database/migrations/2024_12_08_000000_add_profile_fields_to_tutors_table.php` exists

### Documentation
- [ ] `TUTOR_PROFILE_SETUP.md` exists

## Testing Checklist

### Access & Authentication
- [ ] Unauthenticated users cannot access `/tutor/profile`
- [ ] Non-tutor users cannot access tutor profile routes
- [ ] Tutors can access `/tutor/profile` dashboard

### Dashboard
- [ ] Dashboard loads without errors
- [ ] Profile completion percentage displays
- [ ] All 12 section cards display correctly
- [ ] Navigation links work

### Personal Details
- [ ] Form loads with pre-filled data
- [ ] Validation works (test with empty fields)
- [ ] Update saves correctly
- [ ] Success message displays

### Photo Upload
- [ ] File input accepts image files only
- [ ] Preview displays before upload
- [ ] Max file size validation works
- [ ] File saves to correct location

### Video Upload
- [ ] File input accepts video files only
- [ ] Preview displays before upload
- [ ] Video title field works
- [ ] Max file size validation works

### Subjects
- [ ] All subjects display
- [ ] Selected subjects show correctly
- [ ] Level selection works
- [ ] Subject count updates

### Address
- [ ] Form saves address fields
- [ ] Coordinates (lat/lng) accept decimal numbers
- [ ] Validation works

### Education
- [ ] Add education form appears
- [ ] Education entries display in list
- [ ] Edit functionality works
- [ ] Delete removes entry
- [ ] Array index handling works

### Experience
- [ ] Add experience form appears
- [ ] Experience entries display in list
- [ ] Currently working checkbox disables end date
- [ ] Delete removes entry

### Teaching Details
- [ ] Price per hour accepts decimal values
- [ ] Teaching mode checkboxes work
- [ ] Multiple modes can be selected
- [ ] Availability textarea works

### Profile Description
- [ ] Character counters display
- [ ] Real-time preview works
- [ ] Min/max validation enforced
- [ ] Description saves correctly

### Courses
- [ ] Add course form appears
- [ ] Course cards display correctly
- [ ] Price formatting works
- [ ] Delete removes course

### View Profile
- [ ] Public profile displays all sections
- [ ] Video plays correctly
- [ ] All information displays properly
- [ ] Edit profile button appears for owner

### Settings
- [ ] Notification checkboxes work
- [ ] Profile visibility radio buttons work
- [ ] Language dropdown works
- [ ] Settings save correctly

## Performance Checklist

- [ ] Page load times are acceptable
- [ ] No N+1 queries in dashboard
- [ ] File uploads complete successfully
- [ ] Large videos don't timeout

## Security Checklist

- [ ] CSRF tokens present on all forms
- [ ] File uploads validated for type
- [ ] File uploads validated for size
- [ ] Input sanitization working
- [ ] Database transactions secure
- [ ] Password fields not exposed

## Browser Compatibility

- [ ] Chrome/Chromium
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile browsers

## Known Issues & Fixes

### Issue: Routes not found
**Fix:** Ensure `require __DIR__.'/tutor.php';` is in `routes/web.php`

### Issue: Files not uploading
**Fix:** Run `php artisan storage:link` and check directory permissions

### Issue: Profile completion not updating
**Fix:** Clear view cache with `php artisan view:clear`

### Issue: Tutor role not working
**Fix:** Ensure user has 'tutor' role assigned: `$user->assignRole('tutor');`

## Deployment Checklist

- [ ] All migrations run on production
- [ ] Storage linked on production server
- [ ] File permissions set correctly
- [ ] Environment variables configured
- [ ] Cache cleared on production
- [ ] Database backed up
- [ ] SSL certificate configured
- [ ] Rate limiting configured for file uploads

## Post-Deployment

- [ ] Monitor error logs for issues
- [ ] Verify file uploads working
- [ ] Test profile completion calculation
- [ ] Monitor database performance
- [ ] Check storage usage

---

**Status:** ✓ Ready for Implementation
**Last Updated:** December 8, 2024
