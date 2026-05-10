<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1B2A">
    <title>ParSEC Entry Scanner</title>
    <link rel="stylesheet" href="{{ asset('css/param-theme.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="param-dark">
<div class="scanner-page">

    <div class="scanner-header">
        <h1><i class="fas fa-atom"></i> ParSEC Entry Scanner</h1>
        <p>Point camera at visitor QR code</p>
    </div>

    {{-- Live Count --}}
    <div class="live-count-box">
        <div class="label">Currently Inside</div>
        <div class="count" id="live-count">&mdash;</div>
        <div class="time" id="live-time">Updating...</div>
    </div>

    {{-- QR Reader --}}
    <div id="reader"></div>

    {{-- Scan Result --}}
    <div id="result"></div>

    {{-- Logout --}}
    <form method="POST" action="{{ route('scanner.logout') }}" style="margin-top:24px;">
        @csrf
        <button type="submit" class="btn btn-ghost btn-sm">Sign Out</button>
    </form>

</div>

<script>
let lastScan = '';

const scanner = new Html5QrcodeScanner('reader', {
    fps: 10,
    qrbox: { width: 280, height: 280 },
    rememberLastUsedCamera: true,
});

scanner.render(async (decodedText) => {
    if (decodedText === lastScan) return;
    lastScan = decodedText;

    // Extract ticket code from full URL or use as-is
    const code     = decodedText.includes('/') ? decodedText.split('/').pop() : decodedText;
    const resultEl = document.getElementById('result');

    resultEl.innerHTML = '<div class="scan-result" style="background:var(--navy-mid);color:var(--muted);">Verifying...</div>';

    try {
        const res  = await fetch(`/scanner/verify/${code}`);
        const data = await res.json();

        if (data.status === 'success') {
            resultEl.innerHTML = `
                <div class="scan-result scan-success">
                    &#10003; Entry Granted<br>
                    <strong>${data.name}</strong><br>
                    <span style="font-size:14px;">${data.slot}</span>
                </div>`;
            // Vibrate device on success
            if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
        } else if (data.status === 'already_used') {
            resultEl.innerHTML = `
                <div class="scan-result scan-error">
                    &#9888; Already Scanned<br>
                    <span style="font-size:14px;">Checked in at: ${data.checked_in_at || 'Unknown'}</span>
                </div>`;
        } else {
            resultEl.innerHTML = `
                <div class="scan-result scan-error">
                    &#10007; ${data.message}
                </div>`;
        }
    } catch (e) {
        resultEl.innerHTML = `<div class="scan-result scan-error">&#10007; Network error. Check connection.</div>`;
    }

    // Clear result after 5 seconds
    setTimeout(() => {
        lastScan = '';
        resultEl.innerHTML = '';
    }, 5000);
});

// Live occupancy refresh
async function refreshLive() {
    try {
        const res  = await fetch('/scanner/live');
        const data = await res.json();
        document.getElementById('live-count').textContent = data.inside;
        document.getElementById('live-time').textContent  = 'Updated ' + data.updated_at;
    } catch (e) {}
}
refreshLive();
setInterval(refreshLive, 30000);
</script>
</body>
</html>
