<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@hasSection('title') @yield('title') - @endif {{ config('app.name', 'Bot') }}</title>

    <link rel="stylesheet" href="/css/app.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon.png">

    @stack('styles')
</head>

<body>
    <div class="wrapper">
        @auth()
            <div class="text-block">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a href="{{ route('logout') }}">Logout</a>
            </div>
        @endauth
        @yield('content')
    </div>
@stack('scripts')
</body>
</html>
