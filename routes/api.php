<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TutorController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\CreditPackageController;
use App\Http\Controllers\Api\RequirementController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\CmsPageController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\Admin\CoinPackageController as AdminCoinPackageController;
use App\Http\Controllers\Api\EnquiryController;
use App\Http\Controllers\Api\TutorProfileController;
use App\Http\Controllers\Api\Admin\SubjectModuleController;
use App\Http\Controllers\Payment\RazorpayWebhookController;
use App\Http\Controllers\Api\PasswordResetController;

use App\Http\Controllers\Api\EmailVerificationController;

Route::post('register', [AuthController::class,'register'])->middleware('throttle:10,1');
Route::post('login', [AuthController::class,'login'])->middleware('throttle:10,1');
Route::post('validate-referral-code', [AuthController::class, 'validateReferralCode'])->middleware('throttle:20,1');
Route::post('email/send-verification', [EmailVerificationController::class, 'sendVerificationEmail'])->middleware('throttle:5,1');
Route::match(['get', 'post'], 'email/verify', [EmailVerificationController::class, 'verifyEmail'])->middleware('throttle:30,1');
Route::post('email/resend-verification', [EmailVerificationController::class, 'resendVerificationEmail'])->middleware('throttle:5,1');
Route::post('password/forgot', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:5,1');
Route::post('password/reset', [PasswordResetController::class, 'reset'])->middleware('throttle:10,1');

// Google OAuth
Route::post('auth/google/callback', [\App\Http\Controllers\Api\SocialAuthController::class, 'googleCallback']);

Route::get('tutors', [TutorController::class,'index']);
Route::get('tutors/featured', [TutorController::class,'featured']);
Route::get('tutors/{id}', [TutorController::class,'show']);
Route::get('public/tutors/{id}', [TutorController::class,'publicShow']);

// Advanced search endpoints for tutors
Route::get('tutors/nearby', [TutorController::class,'nearby']);
Route::get('tutors/by-location', [TutorController::class,'byLocation']);
Route::get('cms/{slug}', [CmsPageController::class,'show']);
Route::get('credit-packages', [CreditPackageController::class,'index']);
Route::get('subjects', [SubjectController::class,'index']);

Route::get('/search-subjects', [SubjectController::class, 'search']);

// Contact submissions (public)
Route::post('contact-submissions', [\App\Http\Controllers\Api\ContactSubmissionController::class, 'store'])->middleware('throttle:5,1');

// Field Labels API (Public - for form dropdowns)
Route::get('field-labels', function(Request $request) {
    $fieldName = $request->query('field');
    if ($fieldName) {
        return response()->json([
            'field' => $fieldName,
            'labels' => \App\Models\FieldLabel::getFieldLabels($fieldName)
        ]);
    }
    
    // Return all labels grouped by field
    $labels = \App\Models\FieldLabel::where('is_active', true)
        ->orderBy('field_name')
        ->orderBy('order')
        ->get()
        ->groupBy('field_name')
        ->map(function($items) {
            return $items->pluck('label', 'field_value')->toArray();
        });
    
    return response()->json($labels);
});

// Public Tutor Endpoints
Route::get('tutor/levels/all', [TutorProfileController::class, 'getAllLevels']);

// Public Coin Package Endpoint (no auth required - shows base prices)
Route::get('wallet/packages', [WalletController::class, 'packages']);

// Razorpay Webhook and Callback (Public Routes)
Route::post('wallet/webhook', [WalletController::class, 'webhook']); // Razorpay webhook
Route::get('wallet/payment-callback', [WalletController::class, 'paymentCallback']); // Payment redirect callback

Route::middleware('auth:api')->group(function() {

    // Return authenticated user with relationships (used by frontend `fetchUser`)
    Route::get('user', function (Request $request) {
        $user = $request->user();
        
        // Manually load relationships without causing circular references
        $tutor = null;
        if ($user->tutor) {
            $tutor = $user->tutor->only([
                'id', 'user_id', 'headline', 'about', 'experience_years', 
                'price_per_hour', 'teaching_mode', 'city', 'verified', 
                'rating_avg', 'rating_count', 'gender', 'photo', 'moderation_status',
                'current_role', 'speciality', 'strength'
            ]);
            $tutor['photo_url'] = $user->tutor->photo_url;
        }
        
        $student = null;
        if ($user->student) {
            $student = $user->student->only([
                'id', 'user_id', 'grade_level', 'learning_goals', 'preferred_subjects', 'budget_range'
            ]);
        }
        
        $wallet = null;
        if ($user->wallet) {
            $wallet = $user->wallet->only(['id', 'user_id', 'balance']);
        }
        
        // Get roles
        $userRoles = $user->roles->pluck('name')->toArray();
        
        // Build clean response
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'avatar_url' => $user->avatar_url,
            'role' => $user->role,
            'email_verified_at' => $user->email_verified_at,
            'coins' => $user->coins,
            'referral_code' => $user->referral_code,
            'referred_by' => $user->referred_by,
            'tutor' => $tutor,
            'student' => $student,
            'wallet' => $wallet,
            'roles' => $userRoles,
        ]);
    });

    Route::post('logout', [AuthController::class,'logout']);

    // User Activities
    Route::get('user/activities', [\App\Http\Controllers\Api\AuthController::class, 'getUserActivities']);
    Route::get('user/activities/current', [\App\Http\Controllers\Api\AuthController::class, 'getCurrentActivity']);

    // User Management & Enrollment
    Route::post('user/enroll-teacher', [\App\Http\Controllers\Api\UserController::class, 'enrollAsTeacher']);
    Route::post('user/enroll-student', [\App\Http\Controllers\Api\UserController::class, 'enrollAsStudent']);
    Route::put('user/profile', [\App\Http\Controllers\Api\UserController::class, 'updateProfile']);
    Route::post('user/photo', [\App\Http\Controllers\Api\UserController::class, 'uploadPhoto']);
    Route::post('user/phone/send-otp', [\App\Http\Controllers\Api\UserController::class, 'sendPhoneOtp']);
    Route::post('user/phone/verify-otp', [\App\Http\Controllers\Api\UserController::class, 'verifyPhoneOtp']);

    // Profile Management (aliases for frontend compatibility)
    Route::put('profile', [\App\Http\Controllers\Api\UserController::class, 'updateProfile']);
    Route::post('profile/photo', [\App\Http\Controllers\Api\UserController::class, 'uploadPhoto']);
    Route::post('profile/phone/otp', [\App\Http\Controllers\Api\UserController::class, 'sendPhoneOtp']);
    Route::post('profile/phone/verify', [\App\Http\Controllers\Api\UserController::class, 'verifyPhoneOtp']);
    Route::post('profile/email/verification', [\App\Http\Controllers\Api\UserController::class, 'sendEmailVerification']);
    Route::put('profile/location', [\App\Http\Controllers\Api\UserController::class, 'updateLocation']);

    // Student Routes
    Route::prefix('student')->group(function () {
        Route::post('request-tutor', [\App\Http\Controllers\Api\StudentController::class, 'requestTutor']);
        Route::get('requirements', [\App\Http\Controllers\Api\StudentController::class, 'getRequirements']);
        Route::get('requirements/{id}', [\App\Http\Controllers\Api\StudentController::class, 'getRequirement']);
        Route::put('requirements/{id}', [\App\Http\Controllers\Api\StudentController::class, 'updateRequirement']);
        Route::post('requirements/{id}/close', [\App\Http\Controllers\Api\StudentController::class, 'closeRequirement']);
        Route::delete('requirements/{id}', [\App\Http\Controllers\Api\StudentController::class, 'deleteRequirement']);
        
        // New endpoints for refund, viewing interested teachers, and hiring
        Route::get('requirements/{id}/interested-teachers', [\App\Http\Controllers\Api\StudentController::class, 'getInterestedTeachers']);
        Route::post('requirements/{id}/hire-teacher', [\App\Http\Controllers\Api\StudentController::class, 'hireTeacher']);
        
        // Hired tutors
        Route::get('hired-tutors', [\App\Http\Controllers\Api\StudentController::class, 'hiredTutors']);

        // Student Reviews
        Route::get('reviews', [ReviewController::class, 'myReviews']);
        Route::patch('reviews/{review}', [ReviewController::class, 'updateMine']);
    });

    // Teacher Refund Requests Routes
    Route::prefix('tutor')->group(function () {
        Route::get('refunds', [\App\Http\Controllers\Api\TutorRefundController::class, 'myRefunds']);
        Route::get('refunds/{id}', [\App\Http\Controllers\Api\TutorRefundController::class, 'getRefund']);
    });

    Route::post('enquiry/{id}/request-refund', [\App\Http\Controllers\Api\TutorRefundController::class, 'requestRefund']);

    // Admin Refund Management Routes
    Route::middleware('role:admin')->prefix('admin/refunds')->group(function () {
        Route::get('pending', [\App\Http\Controllers\Api\TutorRefundController::class, 'pendingRefunds']);
        Route::get('stats', [\App\Http\Controllers\Api\TutorRefundController::class, 'refundStats']);
        Route::post('{id}/approve', [\App\Http\Controllers\Api\TutorRefundController::class, 'approveRefund']);
        Route::post('{id}/reject', [\App\Http\Controllers\Api\TutorRefundController::class, 'rejectRefund']);
    });

    Route::post('tutors', [TutorController::class,'store']);

    // Student requirements - specific routes MUST come before {id} route
    Route::get('requirements/posting-eligibility', [RequirementController::class,'postingEligibility']);
    Route::get('requirements/nearby', [RequirementController::class,'nearby']);
    Route::get('requirements/by-location', [RequirementController::class,'byLocation']);
    Route::get('requirements/for-me', [RequirementController::class,'forMe']);
    Route::get('requirements', [RequirementController::class,'index']);
    Route::post('requirements', [RequirementController::class,'store']);
    Route::get('requirements/{id}', [RequirementController::class,'show']);

    // Enquiry (lead-based) routes
    Route::get('enquiries/config', [EnquiryController::class, 'config']);
    Route::middleware('role:tutor')->group(function () {
        Route::get('tutor-jobs', [EnquiryController::class, 'index']);
        Route::post('enquiries/{enquiry}/unlock', [EnquiryController::class, 'unlock']);
    });
    Route::get('enquiries/{enquiry}', [EnquiryController::class, 'show']);

    // Reviews
    Route::get('reviews', [ReviewController::class, 'index']);
    Route::post('tutors/{tutor}/reviews', [ReviewController::class,'store']);

    // Tutor Documents (Tutor role)
    Route::middleware('role:tutor')->prefix('tutor/documents')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\TutorDocumentController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\TutorDocumentController::class, 'store']);
        Route::delete('{id}', [\App\Http\Controllers\Api\TutorDocumentController::class, 'destroy']);
    });

    // Coin Wallet Routes
    Route::prefix('wallet')->group(function () {
        Route::get('/', [WalletController::class, 'index']); // Get balance and transactions
        Route::get('payment-history', [WalletController::class, 'paymentHistory']); // Get payment history with filters
        Route::get('payment-transactions', [WalletController::class, 'paymentTransactions']); // PaymentTransaction-only API
        Route::get('coin-transactions', [WalletController::class, 'coinTransactions']); // CoinTransaction-only API with filters
        Route::get('packages', [WalletController::class, 'packages']); // Get coin packages
        Route::get('coin-packages', [WalletController::class, 'coinPackages']); // Get coin packages with dynamic pricing
        Route::post('purchase', [WalletController::class, 'purchaseCoins']); // Create Razorpay order
        Route::post('verify-payment', [WalletController::class, 'verifyPayment']); // Verify payment and credit coins
        Route::post('payment-failed', [WalletController::class, 'markPaymentFailed']); // Mark payment as failed from gateway
        Route::post('payment-cancelled', [WalletController::class, 'cancelledPayment']); // Handle user dismissing payment modal
        Route::get('referral', [WalletController::class, 'getReferralInfo']); // Get referral code and stats
        Route::post('apply-referral', [WalletController::class, 'applyReferralCode']); // Apply referral code
        Route::get('order/{orderId}/status', [WalletController::class, 'getOrderStatus']); // Get order status
        Route::post('order/{orderId}/cancel', [WalletController::class, 'cancelPayment']); // Cancel pending payment
        Route::get('order/{orderId}/receipt', [WalletController::class, 'downloadReceipt']); // Download receipt
            Route::post('order/{orderId}/retry', [WalletController::class, 'retryPayment'])->name('order.retry'); // Retry failed payment
            Route::get('order/{orderId}/check-pending', [WalletController::class, 'checkPendingPayment'])->name('order.check-pending'); // Check pending payment
        
            // Invoice routes
            Route::get('invoices', [WalletController::class, 'getInvoices'])->name('invoices'); // Get all invoices
            Route::get('invoice/{invoiceId}', [WalletController::class, 'getInvoice'])->name('invoice'); // Get invoice details
            Route::get('invoice/{invoiceId}/download', [WalletController::class, 'downloadInvoice'])->name('invoice.download'); // Download invoice PDF
    });

    // Notifications API
    Route::middleware('auth:api')->group(function () {
        Route::get('notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        // Bulk read (supports optional ids[])
        Route::post('notifications/read', [\App\Http\Controllers\Api\NotificationController::class, 'markAllRead']);
        Route::post('notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'markAllRead']);
        // Single read
        Route::post('notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markRead']);
    });

    // Backward/alternate path for receipt downloads used by frontend
    Route::get('orders/{orderId}/receipt', [WalletController::class, 'downloadReceipt']);

    Route::post('payouts', [PayoutController::class,'store']);

    // Tutor Profile API Routes
    Route::middleware('role:tutor')->prefix('tutor/profile')->name('api.tutor.profile.')->group(function () {
        // Personal Details
        Route::get('personal-details', [TutorProfileController::class, 'getPersonalDetails'])->name('personal-details');
        Route::post('personal-details', [TutorProfileController::class, 'updatePersonalDetails'])->name('update-personal-details');

        // Phone
        Route::post('phone/save', [TutorProfileController::class, 'savePhone'])->name('phone.save');
        Route::post('phone/send-otp', [TutorProfileController::class, 'sendPhoneOtp'])->name('phone.send-otp');
        Route::post('phone/verify-otp', [TutorProfileController::class, 'verifyPhoneOtp'])->name('phone.verify-otp');

        // Photo
        Route::get('photo', [TutorProfileController::class, 'getPhoto'])->name('photo');
        Route::post('photo', [TutorProfileController::class, 'updatePhoto'])->name('update-photo');

        // Video
        Route::get('video', [TutorProfileController::class, 'getVideo'])->name('video');
        Route::post('video', [TutorProfileController::class, 'updateVideo'])->name('update-video');

        // Subjects
        Route::get('subjects/all', [TutorProfileController::class, 'getAllSubjects'])->name('subjects.all');
        Route::get('levels/all', [TutorProfileController::class, 'getAllLevels'])->name('levels.all');
        Route::get('subjects', [TutorProfileController::class, 'getSubjects'])->name('subjects');
        Route::post('subjects/add', [TutorProfileController::class, 'addSubject'])->name('subjects.add');
        Route::post('subjects/create', [TutorProfileController::class, 'createSubject'])->name('subjects.create');
        Route::patch('subjects/{subjectId}', [TutorProfileController::class, 'updateSubject'])->name('subjects.update');
        Route::delete('subjects/{subjectId}', [TutorProfileController::class, 'removeSubject'])->name('subjects.remove');

        // Address
        Route::get('address', [TutorProfileController::class, 'getAddress'])->name('address');
        Route::post('address', [TutorProfileController::class, 'updateAddress'])->name('update-address');

        // Education
        Route::get('institutes/search', [TutorProfileController::class, 'searchInstitutes'])->name('institutes.search');
        Route::get('degree-types', [TutorProfileController::class, 'getDegreeTypes'])->name('degree-types');
        Route::get('education', [TutorProfileController::class, 'getEducation'])->name('education');
        Route::post('education', [TutorProfileController::class, 'storeEducation'])->name('store-education');
        Route::post('education/{index}', [TutorProfileController::class, 'updateEducation'])->name('update-education');
        Route::delete('education/{index}', [TutorProfileController::class, 'deleteEducation'])->name('delete-education');

        // Experience
        Route::get('experience', [TutorProfileController::class, 'getExperience'])->name('experience');
        Route::post('experience', [TutorProfileController::class, 'storeExperience'])->name('store-experience');
        Route::post('experience/{index}', [TutorProfileController::class, 'updateExperience'])->name('update-experience');
        Route::delete('experience/{index}', [TutorProfileController::class, 'deleteExperience'])->name('delete-experience');

        // Teaching Details
        Route::get('teaching-details', [TutorProfileController::class, 'getTeachingDetails'])->name('teaching-details');
        Route::post('teaching-details', [TutorProfileController::class, 'updateTeachingDetails'])->name('update-teaching-details');

        // Profile Description
        Route::get('description', [TutorProfileController::class, 'getDescription'])->name('description');
        Route::post('description', [TutorProfileController::class, 'updateDescription'])->name('update-description');

        // Courses
        Route::get('courses', [TutorProfileController::class, 'getCourses'])->name('courses');
        Route::post('courses', [TutorProfileController::class, 'storeCourse'])->name('store-course');
        Route::put('courses/{id}', [TutorProfileController::class, 'updateCourse'])->name('update-course');
        Route::delete('courses/{id}', [TutorProfileController::class, 'deleteCourse'])->name('delete-course');

        // Profile View (Public)
        Route::get('view/{id?}', [TutorProfileController::class, 'viewProfile'])->name('view');

        // Settings
        Route::get('settings', [TutorProfileController::class, 'getSettings'])->name('settings');
        Route::post('settings', [TutorProfileController::class, 'updateSettings'])->name('update-settings');
    });

    // WhatsApp Contact Routes (Public)
    Route::get('tutor/{tutorId}/whatsapp', [TutorProfileController::class, 'getWhatsAppLink'])->name('tutor.whatsapp');
    Route::get('company/whatsapp', [TutorProfileController::class, 'getCompanyWhatsApp'])->name('company.whatsapp');

    Route::middleware('role:admin')->group(function(){
        // Coin Packages (Admin CRUD)
        Route::get('coin-packages', [AdminCoinPackageController::class, 'index']);
        Route::post('coin-packages', [AdminCoinPackageController::class, 'store']);
        Route::put('coin-packages/{coinPackage}', [AdminCoinPackageController::class, 'update']);
        Route::delete('coin-packages/{coinPackage}', [AdminCoinPackageController::class, 'destroy']);
        Route::post('coin-packages/{coinPackage}/toggle-popular', [AdminCoinPackageController::class, 'togglePopular']);
        Route::post('coin-packages/{coinPackage}/toggle-active', [AdminCoinPackageController::class, 'toggleActive']);
        Route::post('credit-packages', [CreditPackageController::class,'store']);
        Route::put('credit-packages/{creditPackage}', [CreditPackageController::class,'update']);
        Route::delete('credit-packages/{creditPackage}', [CreditPackageController::class,'destroy']);

        Route::get('admin/payouts', [PayoutController::class,'index']);
        Route::put('admin/payouts/{payout}', [PayoutController::class,'update']);
        Route::post('admin/reviews/{review}/moderate', [ReviewController::class,'moderate']);

        Route::post('subjects', [SubjectController::class,'store']);
        Route::put('subjects/{subject}', [SubjectController::class,'update']);
        Route::delete('subjects/{subject}', [SubjectController::class,'destroy']);

        Route::get('cms', [CmsPageController::class,'index']);

        // Subject Modules Management
        Route::prefix('admin/subject-modules')->name('admin.modules.')->group(function () {
            Route::get('', [SubjectModuleController::class, 'index'])->name('index');
            Route::post('', [SubjectModuleController::class, 'store'])->name('store');
            Route::get('{id}', [SubjectModuleController::class, 'show'])->name('show');
            Route::put('{id}', [SubjectModuleController::class, 'update'])->name('update');
            Route::delete('{id}', [SubjectModuleController::class, 'destroy'])->name('destroy');

            // Topics
            Route::post('{moduleId}/topics', [SubjectModuleController::class, 'addTopic'])->name('topics.add');
            Route::put('{moduleId}/topics/{topicId}', [SubjectModuleController::class, 'updateTopic'])->name('topics.update');
            Route::delete('{moduleId}/topics/{topicId}', [SubjectModuleController::class, 'deleteTopic'])->name('topics.delete');

            // Competencies
            Route::post('{moduleId}/competencies', [SubjectModuleController::class, 'addCompetency'])->name('competencies.add');
            Route::put('{moduleId}/competencies/{competencyId}', [SubjectModuleController::class, 'updateCompetency'])->name('competencies.update');
            Route::delete('{moduleId}/competencies/{competencyId}', [SubjectModuleController::class, 'deleteCompetency'])->name('competencies.delete');

            // Reorder
            Route::post('subjects/{subjectId}/reorder', [SubjectModuleController::class, 'reorder'])->name('reorder');
        });

        // Tutor Documents Review (Admin)
        Route::prefix('admin/tutor-documents')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Admin\TutorDocumentReviewController::class, 'index']);
            Route::post('{document}/approve', [\App\Http\Controllers\Api\Admin\TutorDocumentReviewController::class, 'approve']);
            Route::post('{document}/reject', [\App\Http\Controllers\Api\Admin\TutorDocumentReviewController::class, 'reject']);
        });

        // Tutor Verification (Admin)
        Route::prefix('admin/tutor')->group(function () {
            Route::get('{user}/verification-status', [\App\Http\Controllers\Api\Admin\TutorVerificationController::class, 'status']);
            Route::post('{user}/email/verify', [\App\Http\Controllers\Api\Admin\TutorVerificationController::class, 'emailVerify']);
            Route::post('{user}/email/unverify', [\App\Http\Controllers\Api\Admin\TutorVerificationController::class, 'emailUnverify']);
            Route::post('{user}/photo/verify', [\App\Http\Controllers\Api\Admin\TutorVerificationController::class, 'photoVerify']);
            Route::post('{user}/photo/unverify', [\App\Http\Controllers\Api\Admin\TutorVerificationController::class, 'photoUnverify']);
            Route::post('{user}/photo/delete', [\App\Http\Controllers\Api\Admin\TutorVerificationController::class, 'photoDelete']);
        });
    });
});

// Notifications API routes are defined within the authenticated group above
// via App\Http\Controllers\Api\NotificationController.
// Removing duplicate inline definitions to avoid route collisions.

Route::post('razorpay/webhook', [RazorpayWebhookController::class, 'handle']);