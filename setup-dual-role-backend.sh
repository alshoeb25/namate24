#!/bin/bash

# Dual Role System - Backend Setup Script
# This script will set up the backend for the dual-role system

echo "=================================="
echo "Dual Role System - Backend Setup"
echo "=================================="
echo ""

# Check if we're in the project root
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the project root."
    exit 1
fi

echo "✓ Project root detected"
echo ""

# Step 1: Create roles
echo "👤 Step 1: Creating roles..."
php artisan db:seed --class=RoleSeeder

if [ $? -eq 0 ]; then
    echo "✓ Roles created successfully"
else
    echo "❌ Role seeding failed!"
    exit 1
fi
echo ""

# Step 2: Run migrations
echo "📦 Step 2: Running migrations..."
php artisan migrate --force

if [ $? -eq 0 ]; then
    echo "✓ Migrations completed successfully"
else
    echo "❌ Migration failed!"
    exit 1
fi
echo ""

# Step 2: Clear cache
echo "🧹 Step 2: Clearing cache..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo "✓ Cache cleared"
echo ""

# Step 4: Display summary
echo "=================================="
echo "✅ Setup Complete!"
echo "=================================="
echo ""
echo "Backend is ready for frontend integration."
echo ""
echo "📋 What was set up:"
echo "  ✓ students table created"
echo "  ✓ student_requirements table updated with new fields"
echo "  ✓ Student model created"
echo "  ✓ UserController created (enrollment endpoints)"
echo "  ✓ StudentController created (requirement endpoints)"
echo "  ✓ API routes registered"
echo "  ✓ User model updated with student relationship"
echo "  ✓ Student role created"
echo ""
echo "🔌 Available API Endpoints:"
echo "  POST   /api/user/enroll-teacher"
echo "  POST   /api/user/enroll-student"
echo "  GET    /api/user (with tutor/student relationships)"
echo "  PUT    /api/user/profile"
echo "  POST   /api/user/photo"
echo "  POST   /api/user/phone/send-otp"
echo "  POST   /api/user/phone/verify-otp"
echo "  POST   /api/student/request-tutor"
echo "  GET    /api/student/requirements"
echo "  GET    /api/student/requirements/{id}"
echo "  PUT    /api/student/requirements/{id}"
echo "  DELETE /api/student/requirements/{id}"
echo ""
echo "📖 Next Steps:"
echo "  1. Test enrollment endpoints with Postman/Insomnia"
echo "  2. Frontend should now work seamlessly"
echo "  3. Review DUAL_ROLE_TESTING_CHECKLIST.md for testing"
echo ""
echo "🎉 Happy coding!"
