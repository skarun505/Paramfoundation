@extends('layouts.app')
@section('title', 'Choose Your Slot')

@section('content')
<section class="slots-hero">
    <h1>Book Your Visit</h1>
    <p>Select a date and time slot to explore ParSEC</p>

    {{-- Date Picker --}}
    <form method="GET" action="{{ route('customer.slots') }}" class="date-picker-wrap">
        <label for="slot-date" class="text-muted text-sm">Date:</label>
        <input type="date"
               id="slot-date"
               name="date"
               value="{{ $date }}"
               min="{{ now()->toDateString() }}"
               class="param-input"
               style="max-width:200px;"
               onchange="this.form.submit()">
    </form>
</section>

<section class="slots-grid">
    @forelse($slots as $slot)
        <div class="slot-card {{ $slot->is_full ? 'slot-full' : '' }}">
            <div class="slot-time">{{ $slot->label }}</div>

            <div class="slot-meta">
                {{ $slot->date->format('D, d M Y') }}
            </div>

            <div class="slot-availability">
                @if($slot->is_full)
                    <span class="badge badge-red">Sold Out</span>
                @elseif($slot->available <= 5)
                    <span class="badge badge-red">Only {{ $slot->available }} left!</span>
                @elseif($slot->available <= 15)
                    <span class="badge badge-gold">{{ $slot->available }} seats left</span>
                @else
                    <span class="badge badge-green">{{ $slot->available }} seats available</span>
                @endif
            </div>

            <div class="slot-price">&#8377;{{ number_format($slot->price) }}</div>

            {{-- Occupancy bar --}}
            <div class="progress-bar">
                @php $pct = $slot->capacity > 0 ? round(($slot->booked / $slot->capacity) * 100) : 0; @endphp
                <div class="progress-fill {{ $pct >= 80 ? 'high' : ($pct >= 50 ? 'medium' : '') }}"
                     style="width: {{ $pct }}%"></div>
            </div>

            @unless($slot->is_full)
                <a href="{{ route('customer.checkout', $slot) }}" class="btn btn-saffron btn-block">
                    Book Now &rarr;
                </a>
            @endunless
        </div>
    @empty
        <div class="empty-state" style="grid-column:1/-1;">
            <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3>No slots available</h3>
            <p>No time slots are available for this date. Please try another date.</p>
        </div>
    @endforelse
</section>
@endsection
