@extends('layouts.admin')
@section('title', 'Booking #' . $booking->id)

@section('content')

{{-- Back + Header --}}
<div style="display:flex; align-items:center; gap:16px; margin-bottom:24px; flex-wrap:wrap;">
    <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline" style="padding:8px 14px;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
    <div style="flex:1;">
        <h1 style="font-size:20px; font-weight:800; color:var(--white); margin:0;">
            Booking #{{ $booking->id }}
        </h1>
        <div class="text-sm text-muted">{{ $booking->created_at->format('l, d M Y \a\t h:i A') }}</div>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
        @if($booking->status === 'paid')
            <span class="badge badge-green" style="font-size:13px; padding:6px 14px;">
                <i class="fas fa-circle-check"></i> Paid
            </span>
        @elseif($booking->status === 'pending')
            <span class="badge badge-gold" style="font-size:13px; padding:6px 14px;">
                <i class="fas fa-clock"></i> Pending
            </span>
        @else
            <span class="badge badge-red" style="font-size:13px; padding:6px 14px;">
                <i class="fas fa-times-circle"></i> Cancelled
            </span>
        @endif
        @if($customerStats['is_returning'])
            <span class="badge badge-purple" style="font-size:12px; padding:5px 12px; background:rgba(139,92,246,0.15); color:#a78bfa; border:1px solid rgba(139,92,246,0.3);">
                <i class="fas fa-repeat"></i> Returning Customer
            </span>
        @else
            <span class="badge" style="font-size:12px; padding:5px 12px; background:rgba(232,119,34,0.12); color:var(--saffron); border:1px solid rgba(232,119,34,0.25);">
                <i class="fas fa-star"></i> New Customer
            </span>
        @endif
    </div>
</div>

<div style="display:grid; grid-template-columns:2fr 1fr; gap:20px;">

{{-- LEFT COLUMN --}}
<div style="display:flex; flex-direction:column; gap:20px;">

    {{-- ① Booking Details --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-ticket"></i> Booking Details</span>
            <span class="text-sm text-muted">Booking #{{ $booking->id }}</span>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
                <div class="text-sm text-muted" style="margin-bottom:3px;">Slot</div>
                <div class="fw-bold">{{ $booking->slot?->label }}</div>
            </div>
            <div>
                <div class="text-sm text-muted" style="margin-bottom:3px;">Date</div>
                <div class="fw-bold">{{ $booking->slot?->date?->format('D, d M Y') }}</div>
            </div>
            <div>
                <div class="text-sm text-muted" style="margin-bottom:3px;">Visitors</div>
                <div class="fw-bold">{{ $booking->quantity }} person(s)</div>
            </div>
            <div>
                <div class="text-sm text-muted" style="margin-bottom:3px;">Amount Paid</div>
                <div class="fw-bold text-saffron" style="font-size:18px;">&#8377;{{ number_format($booking->total_amount) }}</div>
            </div>
        </div>
    </div>

    {{-- ② Payment Info --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-credit-card"></i> Payment Information</span>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <div style="display:flex; justify-content:space-between; padding:10px; background:var(--navy-light); border-radius:8px;">
                <span class="text-sm text-muted">Payment ID</span>
                <span style="font-family:monospace; font-size:13px; color:var(--off-white);">
                    {{ $booking->razorpay_payment_id ?: '—' }}
                </span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px; background:var(--navy-light); border-radius:8px;">
                <span class="text-sm text-muted">Order ID</span>
                <span style="font-family:monospace; font-size:13px; color:var(--off-white);">
                    {{ $booking->razorpay_order_id ?: '—' }}
                </span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px; background:var(--navy-light); border-radius:8px;">
                <span class="text-sm text-muted">Status</span>
                <span class="fw-bold" style="color: {{ $booking->status === 'paid' ? '#4ade80' : 'var(--muted)' }}">
                    {{ ucfirst($booking->status) }}
                </span>
            </div>
            <div style="display:flex; justify-content:space-between; padding:10px; background:var(--navy-light); border-radius:8px;">
                <span class="text-sm text-muted">Booked At</span>
                <span class="text-sm">{{ $booking->created_at->format('d M Y, h:i A') }}</span>
            </div>
        </div>
    </div>

    {{-- ③ UTM Tracking --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-bullseye"></i> Traffic Source</span>
            <span class="badge badge-gold" style="font-size:11px;">UTM</span>
        </div>
        @php
            $src   = strtolower($booking->utm_source ?? 'direct');
            $icon  = match($src) {
                'google'    => 'fab fa-google',
                'facebook'  => 'fab fa-facebook',
                'instagram' => 'fab fa-instagram',
                'whatsapp'  => 'fab fa-whatsapp',
                'twitter'   => 'fab fa-twitter',
                'linkedin'  => 'fab fa-linkedin',
                'youtube'   => 'fab fa-youtube',
                'referral'  => 'fas fa-link',
                default     => 'fas fa-bullseye',
            };
            $color = match($src) {
                'google'    => '#4285F4',
                'facebook'  => '#1877F2',
                'instagram' => '#E1306C',
                'whatsapp'  => '#25D366',
                'twitter'   => '#1DA1F2',
                'linkedin'  => '#0077B5',
                'youtube'   => '#FF0000',
                'direct'    => '#E87722',
                default     => '#8FA3B1',
            };
        @endphp

        {{-- Source Hero --}}
        <div style="display:flex; align-items:center; gap:16px; padding:16px; background:var(--navy-light);
                    border-radius:10px; margin-bottom:16px; border-left:4px solid {{ $color }};">
            <i class="{{ $icon }}" style="font-size:32px; color:{{ $color }};"></i>
            <div>
                <div style="font-size:18px; font-weight:700; color:var(--white); text-transform:capitalize;">
                    {{ $booking->utm_source ?? 'Direct' }}
                </div>
                <div class="text-sm text-muted">Traffic Source</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            @foreach([
                ['label' => 'Medium',   'value' => $booking->utm_medium,   'icon' => 'fas fa-layer-group'],
                ['label' => 'Campaign', 'value' => $booking->utm_campaign, 'icon' => 'fas fa-tags'],
                ['label' => 'Content',  'value' => $booking->utm_content,  'icon' => 'fas fa-file-alt'],
                ['label' => 'Term',     'value' => $booking->utm_term,     'icon' => 'fas fa-key'],
            ] as $row)
            <div style="padding:10px 12px; background:var(--navy-light); border-radius:8px;">
                <div class="text-sm text-muted" style="margin-bottom:3px;">
                    <i class="{{ $row['icon'] }}" style="width:14px;"></i> {{ $row['label'] }}
                </div>
                <div class="fw-bold text-sm">
                    {{ $row['value'] ?: '—' }}
                </div>
            </div>
            @endforeach
        </div>

        @if($booking->referrer || $booking->landing_page)
        <div style="margin-top:12px; display:flex; flex-direction:column; gap:8px;">
            @if($booking->referrer)
            <div style="padding:10px 12px; background:var(--navy-light); border-radius:8px;">
                <div class="text-sm text-muted" style="margin-bottom:3px;">
                    <i class="fas fa-external-link-alt" style="width:14px;"></i> Referrer
                </div>
                <div class="text-sm" style="word-break:break-all; color:var(--off-white);">{{ $booking->referrer }}</div>
            </div>
            @endif
            @if($booking->landing_page)
            <div style="padding:10px 12px; background:var(--navy-light); border-radius:8px;">
                <div class="text-sm text-muted" style="margin-bottom:3px;">
                    <i class="fas fa-map-pin" style="width:14px;"></i> Landing Page
                </div>
                <div class="text-sm" style="word-break:break-all; color:var(--off-white);">{{ $booking->landing_page }}</div>
            </div>
            @endif
        </div>
        @endif
    </div>

    {{-- ④ Tickets --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-qrcode"></i> Tickets ({{ $booking->tickets->count() }})</span>
        </div>
        @forelse($booking->tickets as $i => $ticket)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px;
                    background:var(--navy-light); border-radius:8px; margin-bottom:8px;">
            <div>
                <div class="text-sm text-muted" style="margin-bottom:2px;">Visitor {{ $i + 1 }}</div>
                <div style="font-family:monospace; font-weight:700; font-size:14px; color:var(--saffron);">
                    {{ $ticket->ticket_code }}
                </div>
            </div>
            <div style="text-align:right;">
                @if($ticket->status === 'used')
                    <span class="badge badge-red" style="font-size:11px;">
                        <i class="fas fa-check-double"></i> Scanned
                    </span>
                @elseif($ticket->status === 'valid')
                    <span class="badge badge-green" style="font-size:11px;">
                        <i class="fas fa-circle-check"></i> Valid
                    </span>
                @else
                    <span class="badge badge-gray" style="font-size:11px;">{{ ucfirst($ticket->status) }}</span>
                @endif
            </div>
        </div>
        @empty
        <p class="text-muted text-sm">No tickets generated.</p>
        @endforelse
    </div>

</div>

{{-- RIGHT COLUMN --}}
<div style="display:flex; flex-direction:column; gap:20px;">

    {{-- ⑤ Customer Info --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-user"></i> Customer</span>
        </div>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <div style="text-align:center; padding:16px 0; border-bottom:1px solid var(--navy-light);">
                <div style="width:52px; height:52px; border-radius:50%; background:var(--saffron);
                            display:flex; align-items:center; justify-content:center;
                            font-size:22px; font-weight:800; color:white; margin:0 auto 10px;">
                    {{ strtoupper(substr($booking->name, 0, 1)) }}
                </div>
                <div class="fw-bold" style="font-size:16px;">{{ $booking->name }}</div>
                @if($customerStats['is_returning'])
                    <div class="text-sm" style="color:#a78bfa; margin-top:4px;">
                        <i class="fas fa-repeat"></i> Returning Customer
                    </div>
                @else
                    <div class="text-sm" style="color:var(--saffron); margin-top:4px;">
                        <i class="fas fa-star"></i> New Customer
                    </div>
                @endif
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fas fa-phone" style="color:var(--muted); width:16px;"></i>
                <span class="text-sm">{{ $booking->phone }}</span>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fas fa-envelope" style="color:var(--muted); width:16px;"></i>
                <span class="text-sm">{{ $booking->email }}</span>
            </div>
            @if($customerStats['first_booking_at'])
            <div style="display:flex; align-items:center; gap:10px;">
                <i class="fas fa-calendar" style="color:var(--muted); width:16px;"></i>
                <span class="text-sm text-muted">
                    Customer since {{ $customerStats['first_booking_at']->format('M Y') }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- ⑥ Customer Lifetime Stats --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-chart-line"></i> Lifetime Value</span>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div style="padding:14px; background:var(--navy-light); border-radius:8px; text-align:center;">
                <div style="font-size:22px; font-weight:800; color:var(--saffron);">
                    &#8377;{{ number_format($customerStats['total_revenue']) }}
                </div>
                <div class="text-sm text-muted" style="margin-top:3px;">Total Revenue</div>
            </div>
            <div style="padding:14px; background:var(--navy-light); border-radius:8px; text-align:center;">
                <div style="font-size:22px; font-weight:800; color:var(--white);">
                    {{ $customerStats['paid_bookings'] }}
                </div>
                <div class="text-sm text-muted" style="margin-top:3px;">Paid Bookings</div>
            </div>
            <div style="padding:14px; background:var(--navy-light); border-radius:8px; text-align:center;">
                <div style="font-size:22px; font-weight:800; color:var(--white);">
                    {{ $customerStats['total_visitors'] }}
                </div>
                <div class="text-sm text-muted" style="margin-top:3px;">Total Visitors</div>
            </div>
            <div style="padding:14px; background:var(--navy-light); border-radius:8px; text-align:center;">
                <div style="font-size:22px; font-weight:800; color:var(--white);">
                    {{ $customerStats['total_bookings'] }}
                </div>
                <div class="text-sm text-muted" style="margin-top:3px;">All Bookings</div>
            </div>
        </div>
    </div>

    {{-- ⑦ Customer Booking History --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-history"></i> Booking History</span>
            <span class="badge badge-gray" style="font-size:11px;">{{ $customerBookings->count() }} total</span>
        </div>
        @forelse($customerBookings as $cb)
        <a href="{{ route('admin.bookings.show', $cb->id) }}"
           style="display:block; padding:10px 12px; background:{{ $cb->id === $booking->id ? 'rgba(232,119,34,0.1)' : 'var(--navy-light)' }};
                  border-radius:8px; margin-bottom:6px; text-decoration:none;
                  border-left:3px solid {{ $cb->id === $booking->id ? 'var(--saffron)' : 'transparent' }};
                  transition: all 0.2s;">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div class="text-sm fw-bold">
                        {{ $cb->slot?->date?->format('d M Y') }} — {{ $cb->slot?->label }}
                        @if($cb->id === $booking->id)
                            <span style="color:var(--saffron); font-size:10px;">(current)</span>
                        @endif
                    </div>
                    <div class="text-sm text-muted">{{ $cb->quantity }} visitor(s)</div>
                </div>
                <div style="text-align:right;">
                    <div class="fw-bold text-saffron text-sm">&#8377;{{ number_format($cb->total_amount) }}</div>
                    @if($cb->status === 'paid')
                        <span style="font-size:10px; color:#4ade80;">Paid</span>
                    @else
                        <span style="font-size:10px; color:var(--muted);">{{ ucfirst($cb->status) }}</span>
                    @endif
                </div>
            </div>
        </a>
        @empty
        <p class="text-sm text-muted">No history found.</p>
        @endforelse
    </div>

</div>
</div>

@endsection
