<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="description" content="Namate24.com is a trusted global edtech platform connecting students, teachers, and institutes for online and offline learning. Offering multi-subject training courses, home tutoring, project assistance, and assignment help with reliable support for learners and educators worldwide.">
  <meta name="theme-color" content="#00BCD4">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  
  <!-- Open Graph / Social Media Meta Tags -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="#1 EdTech Platform Connecting Students, Teachers & Institutes">
  <meta property="og:description" content="Namate24.com is a trusted global edtech platform connecting students, teachers, and institutes for online and offline learning. Offering multi-subject training courses, home tutoring, project assistance, and assignment help with reliable support for learners and educators worldwide.">
  <meta property="og:image" content="{{ asset('storage/meta-image.png') }}">
  <meta property="og:url" content="{{ url()->current() }}">
  
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="#1 EdTech Platform Connecting Students, Teachers & Institutes">
  <meta name="twitter:description" content="Namate24.com is a trusted global edtech platform connecting students, teachers, and institutes for online and offline learning.">
  <meta name="twitter:image" content="{{ asset('storage/meta-image.png') }}">
  
  <title>#1 EdTech Platform Connecting Students, Teachers & Institutes</title>
  <link rel="icon" type="image/png" href="{{ asset('storage/fav_icon.png') }}">
  <link rel="apple-touch-icon" href="{{ asset('storage/fav_icon.png') }}">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  @vite('resources/js/app.js')
</head>
<body>
  <script>
    window.NAMATE24 = {
      user: @json(auth()->user())
    };
  </script>

  <!-- Razorpay Checkout -->
  <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

  <div id="app"></div>
</body>
</html>