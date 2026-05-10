@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')

{{-- Stats Cards --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Today's Bookings</div>
        <div class="stat-value">{{ $stats['today_bookings'] }}</div>
        <div class="stat-sub">Paid entries today</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Today's Revenue</div>
        <div class="stat-value">&#8377;{{ number_format($stats['today_revenue']) }}</div>
        <div class="stat-sub">From paid bookings</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Inside Now</div>
        <div class="stat-value" id="live-inside">{{ $stats['inside_now'] }}</div>
        <div class="stat-sub">Visitors currently inside</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">All-Time Visitors</div>
        <div class="stat-value">{{ number_format($stats['total_visitors']) }}</div>
        <div class="stat-sub">Total check-ins</div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; flex-wrap:wrap;">

    {{-- Today's Slots --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Today's Slots</span>
            <a href="{{ route('admin.slots.create') }}" class="btn btn-saffron btn-sm">+ Add Slot</a>
        </div>
        @forelse($slots as $slot)
        <div style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <span style="font-size:14px;font-weight:600;color:var(--white);">{{ $slot->label }}</span>
                <span class="text-sm text-muted">{{ $slot->booked }}/{{ $slot->capacity }}</span>
            </div>
            <div class="progress-bar">
                @php $pct = $slot->capacity > 0 ? round(($slot->booked/$slot->capacity)*100) : 0; @endphp
                <div class="progress-fill {{ $pct>=80?'high':($pct>=50?'medium':'') }}"
                     style="width:{{ $pct }}%"></div>
            </div>
        </div>
        @empty
            <p class="text-muted text-sm">No slots scheduled for today.</p>
        @endforelse
    </div>

    {{-- Recent Bookings --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Bookings</span>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-ghost btn-sm">View All</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="param-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slot</th>
                        <th>Qty</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBookings as $b)
                    <tr>
                        <td>
                            <div style="font-weight:600;">{{ $b->name }}</div>
                            <div class="text-sm text-muted">{{ $b->phone }}</div>
                        </td>
                        <td class="text-sm">{{ $b->slot?->label }}</td>
                        <td>{{ $b->quantity }}</td>
                        <td class="text-saffron fw-bold">&#8377;{{ number_format($b->total_amount) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted text-sm text-center">No bookings yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Refresh "inside now" count every 30 seconds
async function refreshLiveCount() {
    try {
        const res  = await fetch('/scanner/live');
        const data = await res.json();
        const el   = document.getElementById('live-inside');
        if (el) el.textContent = data.inside;
    } catch (e) {}
}
setInterval(refreshLiveCount, 30000);
</script>
@endpush
