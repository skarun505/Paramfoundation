@extends('layouts.app')
@section('title', 'Checkout')

@if(!$dummyMode)
@push('head')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endpush
@endif

@section('content')
<div class="checkout-container">

    {{-- Breadcrumb --}}
    <div style="padding: 24px 0 12px; font-size:13px; color:var(--muted);">
        <a href="{{ route('customer.slots') }}" style="color:var(--muted);">&larr; Back to slots</a>
    </div>

    @if($dummyMode)
    {{-- Demo Mode Banner --}}
    <div class="alert alert-warning" style="margin-bottom:20px; display:flex; align-items:center; gap:10px;">
        <i class="fas fa-flask" style="font-size:18px; flex-shrink:0;"></i>
        <div>
            <strong>Test / Demo Mode</strong> — No real payment required.
            Click <strong>Confirm Booking</strong> to generate a dummy ticket.
        </div>
    </div>
    @endif

    {{-- Slot Summary --}}
    <div class="checkout-summary">
        <h2>Your Visit</h2>
        <p class="slot-info">{{ $slot->label }} &middot; {{ $slot->date->format('l, d M Y') }}</p>
        <div class="slot-price-label">
            &#8377;{{ number_format($slot->price) }}
            <span style="font-size:14px;font-weight:400;color:var(--muted)"> per person</span>
        </div>
        <div style="margin-top:10px;">
            <span class="badge badge-green" style="font-size:11px;">{{ $slot->available }} seats available</span>
        </div>
    </div>

    {{-- Booking Form --}}
    <div class="form-card">
        <h3 style="font-size:17px; font-weight:700; margin-bottom:22px; color:var(--white);">Visitor Details</h3>

        <form id="booking-form" action="{{ route('customer.confirm') }}" method="POST">
            @csrf

            {{-- Hidden fields --}}
            <input type="hidden" name="slot_id"    value="{{ $slot->id }}">
            <input type="hidden" name="dummy_mode" value="{{ $dummyMode ? '1' : '0' }}">

            @if(!$dummyMode)
            <input type="hidden" name="razorpay_order_id"   value="{{ $order['id'] ?? '' }}">
            <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
            <input type="hidden" name="razorpay_signature"  id="razorpay_signature">
            @endif

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="param-input"
                       required placeholder="Your full name"
                       value="{{ old('name') }}">
                @error('name')<div class="text-sm" style="color:var(--red-text);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="phone">Mobile Number (WhatsApp)</label>
                <input type="tel" id="phone" name="phone" class="param-input"
                       required maxlength="10" placeholder="10-digit number"
                       value="{{ old('phone') }}">
                <div class="text-sm text-muted" style="margin-top:4px;">Ticket will be sent to this number</div>
                @error('phone')<div class="text-sm" style="color:var(--red-text);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="param-input"
                       required placeholder="you@example.com"
                       value="{{ old('email') }}">
                @error('email')<div class="text-sm" style="color:var(--red-text);margin-top:4px;">{{ $message }}</div>@enderror
            </div>

            <div class="form-group">
                <label for="qty-select">Number of Visitors</label>
                <select name="quantity" id="qty-select" class="param-input">
                    @for($i = 1; $i <= min(10, $slot->available); $i++)
                        <option value="{{ $i }}" {{ old('quantity') == $i ? 'selected' : '' }}>
                            {{ $i }} {{ $i === 1 ? 'person' : 'people' }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="total-row">
                <span>Total Amount</span>
                <span id="total-display" class="total-amount">
                    &#8377;{{ number_format($slot->price) }}
                </span>
            </div>

            @if($dummyMode)
                {{-- DUMMY MODE: Direct form submit --}}
                <button type="submit" id="pay-btn" class="btn btn-saffron btn-block btn-lg">
                    <i class="fas fa-circle-check"></i> Confirm Booking (Test Mode)
                </button>
                <p class="text-sm text-muted text-center" style="margin-top:12px;">
                    No payment required &mdash; this is a test booking
                </p>
            @else
                {{-- LIVE MODE: Razorpay popup --}}
                <button type="button" id="pay-btn" class="btn btn-saffron btn-block btn-lg"
                        onclick="startPayment()">
                    <i class="fas fa-lock"></i> Pay &amp; Confirm
                </button>
                <p class="text-sm text-muted text-center" style="margin-top:12px;">
                    Secured by Razorpay &middot; UPI, Cards, NetBanking accepted
                </p>
            @endif
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const slotPrice = {{ $slot->price }};

document.getElementById('qty-select').addEventListener('change', function () {
    const total = this.value * slotPrice;
    document.getElementById('total-display').textContent =
        '\u20B9' + total.toLocaleString('en-IN');
});

@if(!$dummyMode)
const orderId = '{{ $order['id'] ?? '' }}';
const rzpKey  = '{{ config('services.razorpay.key') }}';

function startPayment() {
    const qty   = parseInt(document.getElementById('qty-select').value);
    const name  = document.getElementById('name').value;
    const phone = document.getElementById('phone').value;
    const email = document.getElementById('email').value;

    if (!name || !phone || !email) {
        alert('Please fill in all visitor details before proceeding.');
        return;
    }

    document.getElementById('pay-btn').disabled    = true;
    document.getElementById('pay-btn').textContent = 'Opening payment...';

    const rzp = new Razorpay({
        key:         rzpKey,
        amount:      qty * slotPrice * 100,
        currency:    'INR',
        order_id:    orderId,
        name:        'ParSEC \u2014 Param Science Centre',
        description: 'Entry Ticket \u00B7 ' + qty + ' visitor(s)',
        theme:       { color: '#E87722' },
        prefill:     { name, email, contact: '91' + phone },
        handler: function (response) {
            document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
            document.getElementById('razorpay_signature').value  = response.razorpay_signature;
            document.getElementById('booking-form').submit();
        },
        modal: {
            ondismiss: function () {
                document.getElementById('pay-btn').disabled    = false;
                document.getElementById('pay-btn').textContent = 'Pay & Confirm \u2192';
            }
        }
    });
    rzp.open();
}
@endif
</script>
@endpush
