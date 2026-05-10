<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — ParSEC</title>
    <link rel="stylesheet" href="{{ asset('css/param-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="param-dark" style="min-height:100vh; display:flex; align-items:center; justify-content:center;
     background: radial-gradient(ellipse at top, #1A2D42 0%, #0D1B2A 70%);">

<div style="width:100%; max-width:400px; padding: 24px;">

    {{-- Logo --}}
    <div style="text-align:center; margin-bottom:32px;">
        <div style="font-size:48px; margin-bottom:8px; color:var(--saffron);"><i class="fas fa-atom"></i></div>
        <h1 style="font-size:24px; font-weight:800; color:var(--saffron); margin-bottom:4px;">ParSEC Admin</h1>
        <p class="text-muted text-sm">Param Science Experience Centre</p>
    </div>

    {{-- Card --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Sign In</span>
            <span class="badge badge-gold" style="font-size:11px;">Staff Portal</span>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="alert alert-success" style="margin-bottom:16px;">{{ session('status') }}</div>
        @endif

        {{-- Validation Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom:16px;">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       class="param-input" required autofocus
                       value="{{ old('email', 'admin@parsec.in') }}"
                       placeholder="admin@parsec.in">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       class="param-input" required
                       placeholder="••••••••">
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:var(--muted);">
                    <input type="checkbox" name="remember">
                    Remember me
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm" style="color:var(--muted);">
                        Forgot password?
                    </a>
                @endif
            </div>

            <button type="submit" class="btn btn-saffron btn-block btn-lg">
                Sign In to Admin &rarr;
            </button>
        </form>

        <div style="text-align:center; margin-top:20px; padding-top:16px; border-top:1px solid var(--navy-light);">
            <div class="text-sm text-muted" style="margin-bottom:8px;">Default credentials</div>
            <code style="background:var(--navy-light); padding:6px 12px; border-radius:6px; font-size:12px; color:var(--saffron);">
                admin@parsec.in &nbsp;/&nbsp; admin@1234
            </code>
        </div>
    </div>

    <div class="text-sm text-muted text-center" style="margin-top:20px;">
        <a href="{{ route('home') }}" style="color:var(--muted);">&larr; Back to customer site</a>
    </div>
</div>

</body>
</html>
