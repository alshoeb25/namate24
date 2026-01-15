<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Primary Meta Tags -->
    <title>Namate24 - Find Your Perfect Tutor | Online & Home Tutoring Platform</title>
    <meta name="title" content="Namate24 - Find Your Perfect Tutor | Online & Home Tutoring Platform">
    <meta name="description" content="Connect with expert tutors for online and home tutoring. Namate24 offers personalized learning experiences across all subjects and grades. Join thousands of students and tutors worldwide.">
    <meta name="keywords" content="online tutoring, home tutor, find tutor, private tutoring, learn online, education platform, tutor marketplace, namate24">
    <meta name="robots" content="index, follow">
    <meta name="language" content="English">
    <meta name="author" content="Namate24">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="Namate24 - Find Your Perfect Tutor | Online & Home Tutoring Platform">
    <meta property="og:description" content="Connect with expert tutors for online and home tutoring. Namate24 offers personalized learning experiences across all subjects and grades.">
    <meta property="og:image" content="{{ asset('storage/meta_image.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="Namate24">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url('/') }}">
    <meta property="twitter:title" content="Namate24 - Find Your Perfect Tutor | Online & Home Tutoring Platform">
    <meta property="twitter:description" content="Connect with expert tutors for online and home tutoring. Namate24 offers personalized learning experiences across all subjects and grades.">
    <meta property="twitter:image" content="{{ asset('storage/meta_image.png') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('storage/fav_icon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('storage/fav_icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('storage/fav_icon.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('storage/fav_icon.png') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Analytics (Production Only) -->
    @if(app()->environment('production'))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-ZTZPT0K015"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-ZTZPT0K015');
    </script>
    @endif
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
