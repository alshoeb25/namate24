@echo off
REM Dual Role System - Backend Setup Script (Windows)
REM This script will set up the backend for the dual-role system

echo ==================================
echo Dual Role System - Backend Setup
echo ==================================
echo.

REM Check if we're in the project root
if not exist "artisan" (
    echo [X] Error: artisan file not found. Please run this script from the project root.
    exit /b 1
)

echo [√] Project root detected
echo.

REM Step 1: Create roles
echo [User] Step 1: Creating roles...
php artisan db:seed --class=RoleSeeder

if %errorlevel% neq 0 (
    echo [X] Role seeding failed!
    exit /b 1
)
echo [√] Roles created successfully
echo.

REM Step 2: Run migrations
echo [Package] Step 2: Running migrations...
php artisan migrate --force

if %errorlevel% neq 0 (
    echo [X] Migration failed!
    exit /b 1
)
echo [√] Migrations completed successfully
echo.

REM Step 3: Clear cache
echo [Broom] Step 3: Clearing cache...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo [√] Cache cleared
echo.

REM Step 4: Display summary
echo ==================================
echo [√] Setup Complete!
echo ==================================
echo.
echo Backend is ready for frontend integration.
echo.
echo [List] What was set up:
echo   [√] students table created
echo   [√] student_requirements table updated with new fields
echo   [√] Student model created
echo   [√] UserController created (enrollment endpoints)
echo   [√] StudentController created (requirement endpoints)
echo   [√] API routes registered
echo   [√] User model updated with student relationship
echo.
echo [Plug] Available API Endpoints:
echo   POST   /api/user/enroll-teacher
echo   POST   /api/user/enroll-student
echo   GET    /api/user (with tutor/student relationships)
echo   PUT    /api/user/profile
echo   POST   /api/user/photo
echo   POST   /api/user/phone/send-otp
echo   POST   /api/user/phone/verify-otp
echo   POST   /api/student/request-tutor
echo   GET    /api/student/requirements
echo   GET    /api/student/requirements/{id}
echo   PUT    /api/student/requirements/{id}
echo   DELETE /api/student/requirements/{id}
echo.
echo [Book] Next Steps:
echo   1. Test enrollment endpoints with Postman/Insomnia
echo   2. Frontend should now work seamlessly
echo   3. Review DUAL_ROLE_TESTING_CHECKLIST.md for testing
echo.
echo [Party] Happy coding!
echo.
pause
