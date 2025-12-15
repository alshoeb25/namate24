# namate24 - Tutor Marketplace (API + Admin + Frontend Skeleton)

A Laravel 11+ API backend for a tutor-student marketplace with:
- Users: tutors & students (Spatie Permissions)
- Tutor moderation & profiles
- Subject categories
- Credit packages + wallet (Razorpay-ready)
- Student requirements (leads)
- Reviews moderation
- Payout requests
- CMS pages
- Filament admin panel scaffold
- Vue 3 frontend skeleton for search & wallet flows

This repository contains a scaffold to bootstrap development. It is not production-ready out of the box — fill env vars, configure Meilisearch/Razorpay/S3, add tests, and secure credentials before deployment.

Quick start (dev)
1. Clone repository
2. composer install
3. cp .env.example .env and update DB and service credentials
4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed --class=AdminAndSampleSeeder
7. npm install && npm run dev
8. php artisan storage:link
9. Start queue worker: php artisan queue:work
10. Visit /admin (Filament) to use admin panel (create admin user or modify seeder)

Environment variables
- DB_*
- SANCTUM configuration (for SPA auth)
- RAZORPAY_KEY, RAZORPAY_SECRET, RAZORPAY_WEBHOOK_SECRET
- MEILISEARCH_HOST, MEILISEARCH_KEY (optional)
- QUEUE_CONNECTION (redis recommended)
- FILESYSTEM_DRIVER (s3 recommended in prod)

License
MIT