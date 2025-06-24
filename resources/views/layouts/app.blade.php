<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Platform Manajemen Data Toko</title>
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
