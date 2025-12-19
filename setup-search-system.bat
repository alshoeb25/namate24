@echo off
echo ========================================
echo   SEO-Friendly Tutor Search Setup
echo ========================================
echo.

REM Step 1: Run migrations
echo [1/5] Running database migrations...
php artisan migrate --force
if %errorlevel% equ 0 (
    echo [OK] Migrations completed
) else (
    echo [WARNING] Migration failed or already run
)
echo.

REM Step 2: Configure Meilisearch
echo [2/5] Configuring Meilisearch...
php artisan meilisearch:configure
if %errorlevel% equ 0 (
    echo [OK] Meilisearch configured
) else (
    echo [WARNING] Meilisearch configuration failed
)
echo.

REM Step 3: Import tutors
echo [3/5] Importing tutors to Meilisearch...
php artisan scout:import "App\Models\Tutor"
if %errorlevel% equ 0 (
    echo [OK] Tutors imported successfully
) else (
    echo [WARNING] Import failed - check if Meilisearch is running
)
echo.

REM Step 4: Clear cache
echo [4/5] Clearing application cache...
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo [OK] Cache cleared
echo.

REM Step 5: Optional optimization
echo [5/5] Optimize for production?
set /p optimize="Optimize? (y/n): "
if /i "%optimize%"=="y" (
    echo Optimizing application...
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo [OK] Application optimized
)
echo.

REM Summary
echo ========================================
echo   Setup Complete!
echo ========================================
echo.
echo Next Steps:
echo 1. Ensure Meilisearch is running on port 7700
echo 2. Ensure Redis is running on port 6379
echo 3. Test the search at: /tutors
echo 4. Try SEO URLs: /mathematics-tutors-in-delhi
echo.
echo Documentation: Read SEARCH_SYSTEM_README.md
echo.
echo Test API:
echo curl http://localhost/api/tutors?subject=mathematics
echo.
echo Happy Coding!
echo.
pause
