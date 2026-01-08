<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Tutor;
use App\Models\Subject;
use App\Models\Review;
use App\Models\StudentRequirement;
use App\Models\Wallet;
use App\Models\Booking;
use App\Models\CoinTransaction;

class FeaturedTutorsSeeder extends Seeder
{
    public function run(): void
    {
        // Subjects
        $mathematics = Subject::firstOrCreate(['name' => 'Mathematics'], ['description' => 'Math subject']);
        $physics     = Subject::firstOrCreate(['name' => 'Physics'], ['description' => 'Physics subject']);
        $chemistry   = Subject::firstOrCreate(['name' => 'Chemistry'], ['description' => 'Chemistry subject']);
        $english     = Subject::firstOrCreate(['name' => 'English'], ['description' => 'English subject']);
        $biology     = Subject::firstOrCreate(['name' => 'Biology'], ['description' => 'Biology subject']);

        // Student for requirements
        $student = User::firstOrCreate(
            ['email' => 'student.test@namate.com'],
            [
                'name' => 'Test Student',
                'password' => Hash::make('password123'),
                'phone' => '9999999999',
                'role' => 'student',
                'email_verified_at' => now(),
                'coins' => 100,
            ]
        );
        $student->assignRole('student');

        $tutors = [
            [
                'name' => 'Dr. Priya Sharma',
                'email' => 'priya.sharma@namate.com',
                'phone' => '9876543210',
                'avatar' => 'https://randomuser.me/api/portraits/women/45.jpg',
                'headline' => 'Expert Mathematics & Physics Teacher | 15+ Years Experience',
                'about' => 'Passionate educator with PhD in Mathematics. Specialized in making complex concepts simple and engaging. Helped 500+ students achieve their academic goals.',
                'experience_years' => 15,
                'price_per_hour' => 800,
                'teaching_mode' => 'both',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'area' => 'South Delhi',
                'gender' => 'female',
                'subjects' => [$mathematics->id, $physics->id],
                'rating' => 4.9,
                'verified' => true,
                'lat' => 28.5355,
                'lng' => 77.3910,
            ],
            [
                'name' => 'Prof. Rajesh Kumar',
                'email' => 'rajesh.kumar@namate.com',
                'phone' => '9876543211',
                'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg',
                'headline' => 'IIT Graduate | Chemistry & Biology Expert',
                'about' => 'IIT Delhi alumnus with 12 years of teaching experience. Specialized in competitive exam preparation. My students consistently score above 90%.',
                'experience_years' => 12,
                'price_per_hour' => 1000,
                'teaching_mode' => 'online',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'area' => 'Andheri',
                'gender' => 'male',
                'subjects' => [$chemistry->id, $biology->id],
                'rating' => 5.0,
                'verified' => true,
                'lat' => 19.0760,
                'lng' => 72.8777,
            ],
            [
                'name' => 'Ms. Anjali Verma',
                'email' => 'anjali.verma@namate.com',
                'phone' => '9876543212',
                'avatar' => 'https://randomuser.me/api/portraits/women/68.jpg',
                'headline' => 'English Language Specialist | IELTS Trainer',
                'about' => 'Cambridge certified English teacher with 10 years experience. Specialized in spoken English, grammar, and IELTS preparation. Fun and interactive teaching style.',
                'experience_years' => 10,
                'price_per_hour' => 700,
                'teaching_mode' => 'both',
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'area' => 'Koramangala',
                'gender' => 'female',
                'subjects' => [$english->id],
                'rating' => 4.8,
                'verified' => true,
                'lat' => 12.9352,
                'lng' => 77.6245,
            ],
            [
                'name' => 'Mr. Vikram Singh',
                'email' => 'vikram.singh@namate.com',
                'phone' => '9876543213',
                'avatar' => 'https://randomuser.me/api/portraits/men/75.jpg',
                'headline' => 'Mathematics Wizard | JEE & NEET Expert',
                'about' => 'Engineering graduate with passion for mathematics. Successfully coached 200+ students for JEE and NEET. Known for problem-solving techniques and shortcuts.',
                'experience_years' => 8,
                'price_per_hour' => 900,
                'teaching_mode' => 'offline',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'area' => 'Kothrud',
                'gender' => 'male',
                'subjects' => [$mathematics->id, $physics->id],
                'rating' => 4.7,
                'verified' => true,
                'lat' => 18.5074,
                'lng' => 73.8077,
            ],
        ];

        foreach ($tutors as $tutorData) {
            // User
            $user = User::firstOrCreate(
                ['email' => $tutorData['email']],
                [
                    'name' => $tutorData['name'],
                    'password' => Hash::make('password123'),
                    'phone' => $tutorData['phone'],
                    'avatar' => $tutorData['avatar'],
                    'role' => 'tutor',
                    'email_verified_at' => now(),
                    'coins' => 50,
                ]
            );
            $user->assignRole('tutor');

            // Tutor profile (without teaching_mode to avoid cast issues)
            $tutor = Tutor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'headline' => $tutorData['headline'],
                    'about' => $tutorData['about'],
                    'experience_years' => $tutorData['experience_years'],
                    'price_per_hour' => $tutorData['price_per_hour'],
                    'city' => $tutorData['city'],
                    'state' => $tutorData['state'],
                    'area' => $tutorData['area'],
                    'gender' => $tutorData['gender'],
                    'verified' => $tutorData['verified'],
                    'moderation_status' => 'approved',
                    'rating_avg' => $tutorData['rating'],
                    'rating_count' => rand(15, 50),
                    'lat' => $tutorData['lat'],
                    'lng' => $tutorData['lng'],
                    'online_available' => in_array($tutorData['teaching_mode'], ['online', 'both']),
                ]
            );
            DB::table('tutors')->where('id', $tutor->id)->update(['teaching_mode' => $tutorData['teaching_mode']]);

            // Subjects
            $tutor->subjects()->sync($tutorData['subjects']);

            // Wallet
            Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => rand(1000, 5000)]
            );

            // Bookings + Reviews
            $reviewers = [
                ['name' => 'Rahul Mehta', 'email' => 'rahul.m@example.com'],
                ['name' => 'Sneha Patel', 'email' => 'sneha.p@example.com'],
                ['name' => 'Arjun Das', 'email' => 'arjun.d@example.com'],
                ['name' => 'Pooja Joshi', 'email' => 'pooja.j@example.com'],
                ['name' => 'Karan Shah', 'email' => 'karan.s@example.com'],
            ];
            $reviews = [
                ['rating' => 5, 'comment' => 'Excellent teacher! Very patient and explains concepts thoroughly. Highly recommended!'],
                ['rating' => 5, 'comment' => 'Best tutor I\'ve ever had. My grades improved significantly after joining classes.'],
                ['rating' => 5, 'comment' => 'Very knowledgeable and professional. Makes learning enjoyable and easy to understand.'],
                ['rating' => 4, 'comment' => 'Good teaching style. Punctual and well-prepared for every class.'],
                ['rating' => 5, 'comment' => 'Outstanding mentor! Helped me crack my entrance exam. Forever grateful!'],
            ];
            $reviewCount = rand(3, 5);
            for ($i = 0; $i < $reviewCount; $i++) {
                $reviewer = $reviewers[$i];
                $reviewData = $reviews[$i];

                $reviewerUser = User::firstOrCreate(
                    ['email' => $reviewer['email']],
                    [
                        'name' => $reviewer['name'],
                        'password' => Hash::make('password123'),
                        'role' => 'student',
                        'email_verified_at' => now(),
                    ]
                );

                // Booking record to link review
                $booking = Booking::create([
                    'student_id' => $reviewerUser->id,
                    'tutor_id' => $tutor->id,
                    'start_at' => now()->subDays(rand(10, 60)),
                    'end_at' => now()->subDays(rand(1, 9)),
                    'session_price' => $tutorData['price_per_hour'],
                    'status' => 'completed',
                    'payment_status' => 'paid',
                ]);

                Review::firstOrCreate(
                    [
                        'booking_id' => $booking->id,
                        'student_id' => $reviewerUser->id,
                        'tutor_id' => $tutor->id,
                    ],
                    [
                        'rating' => $reviewData['rating'],
                        'comment' => $reviewData['comment'],
                        'created_at' => now()->subDays(rand(1, 90)),
                    ]
                );
            }

            // Requirements (2 each)
            $requirementTypes = ['tutoring', 'group_class', 'online_course'];
            $statuses = ['pending', 'accepted', 'completed'];
            for ($i = 0; $i < 2; $i++) {
                $status = $statuses[array_rand($statuses)];
                $subject = Subject::find($tutorData['subjects'][array_rand($tutorData['subjects'])]);

                StudentRequirement::create([
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'budget' => $tutorData['price_per_hour'],
                    'budget_type' => 'hourly',
                    'mode' => $tutorData['teaching_mode'],
                    'service_type' => $requirementTypes[array_rand($requirementTypes)],
                    'details' => "Need help with {$subject->name} for Class 10 CBSE board exam preparation. Looking for regular classes.",
                    'city' => $tutorData['city'],
                    'area' => $tutorData['area'],
                    'location' => $tutorData['city'],
                    'status' => $status,
                    'visible' => true,
                    'posted_at' => now()->subDays(rand(1, 30)),
                ]);
            }

            // Coin transactions (3 each)
            $transactionTypes = ['coin_purchase', 'enquiry_fee', 'tutor_payment'];
            for ($i = 0; $i < 3; $i++) {
                $type = $transactionTypes[array_rand($transactionTypes)];
                $amount = rand(100, 1000);
                CoinTransaction::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'amount' => $amount,
                    'balance_after' => $amount + rand(500, 1500),
                    'description' => 'Test transaction for ' . $tutorData['name'],
                    'payment_id' => 'pay_' . Str::random(16),
                    'order_id' => 'ord_' . Str::random(12),
                    'created_at' => now()->subDays(rand(1, 60)),
                ]);
            }

            $this->command->info("Created tutor: {$tutorData['name']} with reviews and requirements");
        }

        $this->command->info('✅ Featured tutors seeding completed!');
        $this->command->info('📧 All test accounts use password: password123');
        $this->command->info('🔹 Student account: student.test@namate.com');
    }
}
