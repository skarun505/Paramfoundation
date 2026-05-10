@extends('layouts.app')
@section('title', 'Welcome')

@section('content')
<section class="hero">
    <div class="hero-eyebrow">Param Foundation &middot; Bengaluru</div>
    <h1>Explore <span>ParSEC</span><br>Science Centre</h1>
    <p>Book your timed entry ticket online. Skip queues. Experience science.</p>
    <div class="hero-actions">
        <a href="{{ route('customer.slots') }}" class="btn btn-saffron btn-lg">
            <i class="fas fa-ticket"></i> Book Tickets
        </a>
        <a href="#features" class="btn btn-outline btn-lg">Learn More</a>
    </div>
</section>

{{-- Features --}}
<section id="features" style="padding: 60px 24px; max-width: 1100px; margin: 0 auto;">
    <div style="text-align:center; margin-bottom: 40px;">
        <h2 style="font-size:28px; font-weight:800; color:var(--white);">Why book online?</h2>
        <p class="text-muted" style="margin-top:8px;">Guaranteed entry at your preferred time</p>
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:20px;">
        @foreach([
            ['icon'=>'fas fa-ticket','title'=>'Skip the Queue','desc'=>'No waiting in line — your timed slot guarantees entry.'],
            ['icon'=>'fab fa-whatsapp','title'=>'WhatsApp Ticket','desc'=>'QR ticket delivered to your WhatsApp instantly.'],
            ['icon'=>'fas fa-credit-card','title'=>'Secure Payment','desc'=>'Razorpay-powered checkout with UPI, cards & more.'],
            ['icon'=>'fas fa-clock','title'=>'Timed Slots','desc'=>'Choose a convenient time — morning, afternoon or evening.'],
        ] as $f)
        <div class="card animate-fade-in" style="text-align:center;">
            <div style="font-size:32px; margin-bottom:12px; color:var(--saffron);">
                <i class="{{ $f['icon'] }}"></i>
            </div>
            <div style="font-weight:700; font-size:16px; color:var(--white); margin-bottom:8px;">{{ $f['title'] }}</div>
            <div class="text-muted text-sm">{{ $f['desc'] }}</div>
        </div>
        @endforeach
    </div>
</section>

{{-- CTA Banner --}}
<section style="background: linear-gradient(135deg, var(--saffron), var(--saffron-dark)); padding: 40px 24px; text-align:center; margin: 20px 0;">
    <h2 style="font-size:24px; font-weight:800; color:white; margin-bottom:12px;">
        Ready to explore ParSEC?
    </h2>
    <p style="color:rgba(255,255,255,0.85); margin-bottom:20px;">Entry tickets available from &#8377;150</p>
    <a href="{{ route('customer.slots') }}" class="btn btn-lg" style="background:white; color:var(--saffron);">
        <i class="fas fa-arrow-right"></i> Book Now
    </a>
</section>
@endsection
