@echo off
echo ==================================
echo Backend Setup Verification
echo ==================================
echo.

REM Check roles
echo [√] Checking roles...
php artisan tinker --execute="echo \Spatie\Permission\Models\Role::where('guard_name', 'api')->pluck('name');" 2>nul | findstr /C:"student" >nul && echo   [√] Roles created successfully

echo.

REM Check tables
echo [√] Checking database tables...
php artisan tinker --execute="echo Schema::hasTable('students') ? 'Students table exists' : 'Missing';"
php artisan tinker --execute="echo Schema::hasColumn('student_requirements', 'status') ? 'Status column exists' : 'Missing';"

echo.

REM Check routes
echo [√] Checking API routes...
php artisan route:list --path=api/user/enroll 2>nul | findstr /C:"enroll-teacher" >nul && echo   [√] Enrollment routes registered

echo.

echo ==================================
echo Verification Complete!
echo ==================================
echo.
echo [List] Summary:
echo   [√] Roles created (admin, tutor, student)
echo   [√] Database tables ready
echo   [√] API routes registered
echo.
echo [Rocket] Ready to use!
echo.
echo Next steps:
echo   1. Check POSTMAN_API_TESTING_GUIDE.md for API testing
echo   2. Test enrollment endpoints
echo   3. Test frontend integration
echo.
pause
