<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Namate24</title>
  @vite('resources/js/app.js')
</head>
<body>
  <script>
    window.NAMATE24 = {
      user: @json(auth()->user())
    };
  </script>

  <div id="app"></div>
</body>
</html>