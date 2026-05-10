<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ParSEC Scanner — PIN Login</title>
    <link rel="stylesheet" href="{{ asset('css/param-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="param-dark">
<div class="scanner-page" style="justify-content:center;">
    <div style="width:100%;max-width:360px;">
        <div class="scanner-header" style="margin-bottom:32px;">
            <h1><i class="fas fa-atom"></i> ParSEC</h1>
            <p>Staff Entry Scanner — PIN Login</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('scanner.login.post') }}">
                @csrf
                <div class="form-group">
                    <label for="pin">4-Digit Staff PIN</label>
                    <input type="password" id="pin" name="pin"
                           class="param-input"
                           maxlength="4" inputmode="numeric" pattern="\d{4}"
                           placeholder="&#9679;&#9679;&#9679;&#9679;"
                           style="font-size:28px; letter-spacing:12px; text-align:center;"
                           autofocus required>
                    @error('pin')
                        <div class="text-sm" style="color:var(--red-text);margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-saffron btn-block btn-lg" style="margin-top:8px;">
                    Access Scanner
                </button>
            </form>
        </div>

        <p class="text-sm text-muted text-center" style="margin-top:20px;">
            ParSEC Entry Management &middot; Staff Only
        </p>
    </div>
</div>
</body>
</html>
