<!DOCTYPE html>
<html>
<head>
    <title>API SmartRanks Documentation</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('favicon/favicon.ico') }}" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon/favicon.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}" />
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}" />
    <link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}" />

    @vite(['resources/js/swagger.js'])
</head>
<body>
    <div id="swagger-ui"></div>
</body>
</html>