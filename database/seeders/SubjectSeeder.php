<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [

            /* =======================
             | School Subjects
             ======================= */
            'Mathematics',
            'English',
            'Hindi',
            'Science',
            'Physics',
            'Chemistry',
            'Biology',
            'Environmental Studies (EVS)',
            'Social Science',
            'History',
            'Geography',
            'Civics',
            'Political Science',
            'Economics',
            'Business Studies',
            'Accountancy',
            'Statistics',
            'Computer Science',
            'Information Technology',
            'General Knowledge',
            'Moral Science',
            'Sanskrit',
            'French',
            'German',
            'Spanish',

            /* =======================
             | Competitive Exams
             ======================= */
            'IIT JEE Mathematics',
            'IIT JEE Physics',
            'IIT JEE Chemistry',
            'NEET Physics',
            'NEET Chemistry',
            'NEET Biology',
            'UPSC History',
            'UPSC Geography',
            'UPSC Polity',
            'UPSC Economy',
            'UPSC Environment',
            'SSC Reasoning',
            'SSC Quantitative Aptitude',
            'SSC English',
            'Banking Reasoning',
            'Banking Quantitative Aptitude',
            'Banking English',
            'Railway Exams',
            'Defence Exams',
            'CAT Quantitative Aptitude',
            'CAT Logical Reasoning',
            'CAT Verbal Ability',

            /* =======================
             | Programming & IT
             ======================= */
            'Programming Fundamentals',
            'C Programming',
            'C++ Programming',
            'Java',
            'Python',
            'JavaScript',
            'PHP',
            'Laravel',
            'Node.js',
            'Vue.js',
            'React.js',
            'Angular',
            'HTML',
            'CSS',
            'Bootstrap',
            'Tailwind CSS',
            'MySQL',
            'PostgreSQL',
            'MongoDB',
            'Firebase',
            'Data Structures',
            'Algorithms',
            'Operating Systems',
            'Computer Networks',
            'DBMS',
            'System Design',
            'REST API Development',
            'Git & GitHub',
            'Docker Basics',
            'AWS Basics',

            /* =======================
             | Commerce & Management
             ======================= */
            'Financial Accounting',
            'Cost Accounting',
            'Management Accounting',
            'Micro Economics',
            'Macro Economics',
            'Business Economics',
            'Corporate Finance',
            'Marketing Management',
            'Human Resource Management',
            'Operations Management',
            'Entrepreneurship',

            /* =======================
             | Arts, Languages & Skills
             ======================= */
            'Spoken English',
            'Communication Skills',
            'Personality Development',
            'Public Speaking',
            'Creative Writing',
            'Content Writing',
            'Drawing',
            'Painting',
            'Music Vocal',
            'Music Instrumental',
            'Dance',
            'Yoga',
            'Physical Education',
            'Photography',
            'Video Editing',

            /* =======================
             | Professional & Others
             ======================= */
            'Digital Marketing',
            'SEO',
            'Social Media Marketing',
            'Graphic Design',
            'UI UX Design',
            'MS Excel',
            'MS Word',
            'MS PowerPoint',
            'Soft Skills',
            'Interview Preparation',
            'Resume Building',
        ];

        foreach ($subjects as $subject) {
            DB::table('subjects')->updateOrInsert(
                ['slug' => Str::slug($subject)],
                [
                    'name' => $subject,
                    'slug' => Str::slug($subject),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
