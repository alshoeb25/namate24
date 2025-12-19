# 🧪 Dual Role System - Testing Checklist

## Pre-Testing Setup

### Backend Requirements
- [ ] Database migrations run successfully
- [ ] User model has `tutor()` and `student()` relationships
- [ ] All API endpoints implemented and tested in Postman/Insomnia
- [ ] Seed data created (optional but recommended)

### Frontend Requirements
- [ ] `npm install` completed
- [ ] `npm run dev` running successfully
- [ ] No console errors on app load
- [ ] User can login with existing account

---

## 🧑‍🏫 Teacher Role Tests

### Test 1: New User Registration as Teacher
**Steps:**
1. Navigate to `/register`
2. Click "Teacher / Tutor" role option
3. Fill form: Name, Email, Password
4. Click Register

**Expected:**
- ✅ User created in `users` table
- ✅ Tutor record created in `tutors` table with `user_id`
- ✅ Redirected to `/tutor/profile`
- ✅ AuthTopBar shows "Tutor Dashboard" option
- ✅ User menu Teacher section shows checkmark or active indicator

**Actual Result:** ________

---

### Test 2: Access Teacher Routes
**Steps:**
1. Login as teacher user
2. Click avatar → "Tutor Dashboard"
3. Visit `/tutor/profile`
4. Visit `/tutor/dashboard`

**Expected:**
- ✅ Routes accessible without redirect
- ✅ Secondary menu shows teacher navigation
- ✅ Can view/edit tutor profile
- ✅ Dashboard shows teacher-specific content

**Actual Result:** ________

---

### Test 3: Teacher Enrolls as Student
**Steps:**
1. Login as teacher user (has tutor record only)
2. Click avatar → User menu opens
3. Verify "Tutor Dashboard" link visible
4. Verify "Enroll as Student" button visible
5. Click "Enroll as Student"
6. Modal appears with student benefits
7. Click "Enroll as Student" button in modal

**Expected:**
- ✅ Modal shows loading spinner
- ✅ API call to `/api/user/enroll-student` succeeds
- ✅ Student record created in database
- ✅ Success animation shows
- ✅ Redirected to `/student/dashboard` after 2 seconds
- ✅ User menu now shows both "Tutor Dashboard" and "Student Dashboard"

**Actual Result:** ________

---

## 🎓 Student Role Tests

### Test 4: New User Registration as Student
**Steps:**
1. Navigate to `/register`
2. Click "Student / Parent" role option
3. Fill form: Name, Email, Password
4. Click Register

**Expected:**
- ✅ User created in `users` table
- ✅ Student record created in `students` table with `user_id`
- ✅ Redirected to `/student/dashboard`
- ✅ AuthTopBar shows "Student Dashboard" option
- ✅ User menu Student section shows checkmark or active indicator

**Actual Result:** ________

---

### Test 5: Access Student Routes
**Steps:**
1. Login as student user
2. Click avatar → "Student Dashboard"
3. Visit `/student/dashboard`
4. Visit `/student/request-tutor`
5. Visit `/student/requirements`
6. Visit `/student/wallet`

**Expected:**
- ✅ All routes accessible without redirect
- ✅ Secondary menu shows student navigation
- ✅ Dashboard shows 8 cards with proper styling
- ✅ Request form has 12 steps

**Actual Result:** ________

---

### Test 6: Submit 12-Step Request Form
**Steps:**
1. Login as student user
2. Navigate to `/student/request-tutor`
3. Fill all 12 steps:
   - Step 1: Location
   - Step 2: Phone Number
   - Step 3: Details
   - Step 4: Subjects (multiple select)
   - Step 5: Academic Level
   - Step 6: Service Type
   - Step 7: Meeting Options
   - Step 8: Budget
   - Step 9: Gender Preference
   - Step 10: Availability (multiple days)
   - Step 11: Languages (multiple select)
   - Step 12: Tutor Location Preference
4. Click Submit

**Expected:**
- ✅ All validation passes
- ✅ API POST to `/api/student/request-tutor` succeeds
- ✅ Success message displays
- ✅ Redirected to `/student/requirements`
- ✅ New requirement visible in list

**Actual Result:** ________

---

### Test 7: Student Enrolls as Teacher
**Steps:**
1. Login as student user (has student record only)
2. Click avatar → User menu opens
3. Verify "Student Dashboard" link visible
4. Verify "Enroll as Teacher" button visible
5. Click "Enroll as Teacher"
6. Modal appears with teacher benefits
7. Click "Enroll as Teacher" button in modal

**Expected:**
- ✅ Modal shows loading spinner
- ✅ API call to `/api/user/enroll-teacher` succeeds
- ✅ Tutor record created in database
- ✅ Success animation shows
- ✅ Redirected to `/tutor/profile` after 2 seconds
- ✅ User menu now shows both "Student Dashboard" and "Tutor Dashboard"

**Actual Result:** ________

---

## 🔄 Dual Role Tests

### Test 8: User with Both Roles - Navigation
**Steps:**
1. Login as user with both tutor and student records
2. Click avatar → User menu opens
3. Verify both dashboard links visible

**Expected:**
- ✅ "Tutor Dashboard" link present under Teacher section
- ✅ "Student Dashboard" link present under Student section
- ✅ No "Enroll" buttons (already enrolled)
- ✅ Both sections have colored hover effects (pink/blue)

**Actual Result:** ________

---

### Test 9: Dual Role - Route Access
**Steps:**
1. Login as dual role user
2. Visit `/tutor/profile` → Should work
3. Visit `/tutor/dashboard` → Should work
4. Visit `/student/dashboard` → Should work
5. Visit `/student/request-tutor` → Should work

**Expected:**
- ✅ All teacher routes accessible
- ✅ All student routes accessible
- ✅ No redirects or permission errors
- ✅ Proper secondary menus show based on route

**Actual Result:** ________

---

### Test 10: Dual Role - Profile Management
**Steps:**
1. Login as dual role user
2. Navigate to `/profile`
3. Upload profile photo
4. Change name
5. Change email (should trigger verification)
6. Change phone (should trigger OTP)
7. Save changes

**Expected:**
- ✅ Photo uploads successfully
- ✅ Photo visible in AuthTopBar immediately
- ✅ Email change triggers verification link
- ✅ Phone change shows OTP input
- ✅ Changes saved to database
- ✅ User object refreshed in store

**Actual Result:** ________

---

## 🚫 Route Protection Tests

### Test 11: Non-Teacher Accessing Teacher Routes
**Steps:**
1. Login as student-only user (no tutor record)
2. Manually navigate to `/tutor/profile` in browser

**Expected:**
- ✅ Navigation guard catches the access attempt
- ✅ User redirected to `/` (home page)
- ✅ Toast/alert message (optional): "Please enroll as teacher first"

**Actual Result:** ________

---

### Test 12: Non-Student Accessing Student Routes
**Steps:**
1. Login as teacher-only user (no student record)
2. Manually navigate to `/student/dashboard` in browser

**Expected:**
- ✅ Navigation guard catches the access attempt
- ✅ User redirected to `/` (home page)
- ✅ Toast/alert message (optional): "Please enroll as student first"

**Actual Result:** ________

---

### Test 13: Unauthenticated User
**Steps:**
1. Logout (clear token)
2. Manually navigate to `/tutor/profile`
3. Manually navigate to `/student/dashboard`
4. Manually navigate to `/profile`

**Expected:**
- ✅ All attempts redirect to `/login`
- ✅ Return URL stored (optional)
- ✅ After login, redirect to intended page

**Actual Result:** ________

---

## 🎨 UI/UX Tests

### Test 14: Enrollment Modal - Teacher
**Steps:**
1. Login as student-only user
2. Click avatar → "Enroll as Teacher"

**Expected:**
- ✅ Modal appears with fade-in animation
- ✅ Pink/purple gradient header
- ✅ Teacher icon (chalkboard-teacher) visible
- ✅ Benefits list shows:
  - Access to tutor dashboard
  - Create teaching profile
  - Respond to student requirements
  - Connect with students
- ✅ "Enroll as Teacher" button styled correctly
- ✅ Close X button works
- ✅ Click outside modal closes it

**Actual Result:** ________

---

### Test 15: Enrollment Modal - Student
**Steps:**
1. Login as teacher-only user
2. Click avatar → "Enroll as Student"

**Expected:**
- ✅ Modal appears with fade-in animation
- ✅ Blue/cyan gradient header
- ✅ Student icon (user-graduate) visible
- ✅ Benefits list shows:
  - Access to student dashboard
  - Search and find tutors
  - Post tutor requirements
  - Connect with teachers
- ✅ "Enroll as Student" button styled correctly
- ✅ Close X button works
- ✅ Click outside modal closes it

**Actual Result:** ________

---

### Test 16: Enrollment Modal - States
**Steps:**
1. Click "Enroll as Teacher/Student"
2. Observe loading state
3. Observe success state (if API succeeds)
4. Simulate API error (network off or wrong endpoint)

**Expected:**
- ✅ **Loading State:**
  - Spinner animation shows
  - "Processing your enrollment..." message
  - Buttons disabled
  - Can't close modal during loading
- ✅ **Success State:**
  - Large checkmark icon (green)
  - "Enrollment Successful!" message
  - "Redirecting..." text
  - Auto-redirect after 2 seconds
- ✅ **Error State:**
  - Red alert box appears
  - Error message displays
  - Can try again (buttons re-enabled)
  - Can close modal

**Actual Result:** ________

---

### Test 17: User Menu Dropdown Styling
**Steps:**
1. Login as any user
2. Click avatar to open menu
3. Hover over menu items
4. Check responsive design (mobile/tablet/desktop)

**Expected:**
- ✅ Dropdown positioned correctly (not cut off)
- ✅ Z-index high enough (above other elements)
- ✅ Teacher section has pink hover effect
- ✅ Student section has blue hover effect
- ✅ Icons display properly (FontAwesome)
- ✅ Mobile: Menu adjusts to screen size
- ✅ Click outside closes dropdown

**Actual Result:** ________

---

## 📱 Responsive Design Tests

### Test 18: Mobile - User Menu
**Steps:**
1. Open in mobile view (DevTools → 375px width)
2. Login
3. Click avatar

**Expected:**
- ✅ Dropdown appears fully visible
- ✅ Text not truncated
- ✅ Buttons fully clickable
- ✅ Modal works on mobile

**Actual Result:** ________

---

### Test 19: Mobile - Request Form
**Steps:**
1. Mobile view
2. Navigate to `/student/request-tutor`
3. Fill all 12 steps

**Expected:**
- ✅ Form responsive on small screens
- ✅ Progress bar visible and accurate
- ✅ Input fields properly sized
- ✅ Multi-select works (subjects, availability, languages)
- ✅ Navigation buttons accessible

**Actual Result:** ________

---

### Test 20: Tablet - Dual Dashboard
**Steps:**
1. Tablet view (768px width)
2. Login as dual role user
3. View student dashboard
4. Switch to tutor dashboard

**Expected:**
- ✅ Dashboard cards layout properly (2-3 columns)
- ✅ Secondary menus responsive
- ✅ No horizontal scroll
- ✅ Icons and text readable

**Actual Result:** ________

---

## 🔧 Data Validation Tests

### Test 21: Registration Validation
**Steps:**
1. Try to register with:
   - Empty name
   - Invalid email format
   - Short password (< 8 chars)
   - Mismatched password confirmation

**Expected:**
- ✅ Each validation error displays clearly
- ✅ Form submission blocked
- ✅ Red border on invalid fields
- ✅ Error messages specific to issue

**Actual Result:** ________

---

### Test 22: Request Form Validation
**Steps:**
1. Try to proceed through request form steps with:
   - Empty location (step 1)
   - Invalid phone format (step 2)
   - No subjects selected (step 4)
   - Budget = 0 (step 8)

**Expected:**
- ✅ Can't proceed to next step if validation fails
- ✅ Error messages display under invalid fields
- ✅ Form highlights incomplete steps
- ✅ Submit button disabled until all valid

**Actual Result:** ________

---

### Test 23: Profile Update Validation
**Steps:**
1. Navigate to `/profile`
2. Try to update:
   - Email to invalid format
   - Phone to invalid format
   - Upload file > 2MB

**Expected:**
- ✅ Email validation catches format errors
- ✅ Phone validation enforces format
- ✅ File size validation prevents large uploads
- ✅ Errors display before API call

**Actual Result:** ________

---

## 🔐 Security Tests

### Test 24: Token Expiration
**Steps:**
1. Login successfully
2. Manually expire token (delete from localStorage or wait for expiration)
3. Try to access protected route

**Expected:**
- ✅ API returns 401 Unauthorized
- ✅ User redirected to `/login`
- ✅ Token cleared from storage
- ✅ Error message shown

**Actual Result:** ________

---

### Test 25: Duplicate Enrollment Prevention
**Steps:**
1. Login as teacher (already has tutor record)
2. Manually call `userStore.enrollAsTeacher()` in console

**Expected:**
- ✅ API returns error: "Already enrolled as teacher"
- ✅ No duplicate tutor record created
- ✅ Frontend handles error gracefully

**Actual Result:** ________

---

## 🐛 Edge Cases

### Test 26: Network Error During Enrollment
**Steps:**
1. Open DevTools → Network tab
2. Set to "Offline"
3. Try to enroll as teacher/student

**Expected:**
- ✅ Modal shows error message
- ✅ "Network error" or similar message
- ✅ User can close modal
- ✅ Can try again when online

**Actual Result:** ________

---

### Test 27: Rapid Role Switching
**Steps:**
1. Login as dual role user
2. Quickly navigate: 
   - `/tutor/dashboard`
   - `/student/dashboard`
   - `/tutor/profile`
   - `/student/requirements`
3. Repeat rapidly (5 times)

**Expected:**
- ✅ No race conditions
- ✅ Routes load correctly each time
- ✅ No console errors
- ✅ Secondary menus update properly

**Actual Result:** ________

---

### Test 28: Browser Back Button
**Steps:**
1. Navigate through: Home → Login → Dashboard → Profile
2. Click browser back button repeatedly

**Expected:**
- ✅ Navigation stack correct
- ✅ Auth state preserved
- ✅ No infinite redirects
- ✅ Proper page restoration

**Actual Result:** ________

---

## 📊 Test Summary

### Overall Results
- **Total Tests:** 28
- **Passed:** ____
- **Failed:** ____
- **Blocked:** ____

### Critical Issues Found
1. _______________
2. _______________
3. _______________

### Minor Issues Found
1. _______________
2. _______________
3. _______________

### Notes
_____________________
_____________________
_____________________

---

## ✅ Sign-Off

**Tester Name:** _______________
**Date:** _______________
**Build Version:** _______________
**Status:** [ ] Approved  [ ] Needs Fixes  [ ] Blocked

**Recommendations:**
_____________________
_____________________
_____________________
