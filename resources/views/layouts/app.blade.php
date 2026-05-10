<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ParSEC — Param Science Experience Centre, Bengaluru. Book your entry ticket online.">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ParSEC Tickets — @yield('title', 'Book Now')</title>
    <link rel="stylesheet" href="{{ asset('css/param-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @stack('head')
</head>
<body class="param-dark">

    <nav class="param-nav">
        <a href="{{ route('home') }}" class="param-logo">
            <span><i class="fas fa-atom"></i> ParSEC</span>
        </a>
        <div class="nav-links">
            <a href="{{ route('customer.slots') }}" class="btn btn-saffron">
                <i class="fas fa-ticket"></i> Book Tickets
            </a>
        </div>
    </nav>

    <main>
        @if(session('success'))
            <div style="padding: 0 32px; margin-top: 16px;">
                <div class="alert alert-success">{{ session('success') }}</div>
            </div>
        @endif
        @if(session('error'))
            <div style="padding: 0 32px; margin-top: 16px;">
                <div class="alert alert-danger">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="param-footer">
        <p>&copy; Param Foundation &middot; ParSEC Science Experience Centre, Bengaluru</p>
        <p class="text-sm" style="margin-top:6px;">For queries: tickets@paramfoundation.org</p>
    </footer>

    @stack('scripts')
</body>
</html>
