@extends('layouts.admin')
@section('title', 'Reports')

@section('content')

{{-- Date Range Filter --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="min-width:160px; margin-bottom:0;">
            <label style="font-size:12px;color:var(--muted);">From</label>
            <input type="date" name="from" class="param-input" value="{{ $from }}">
        </div>
        <div class="form-group" style="min-width:160px; margin-bottom:0;">
            <label style="font-size:12px;color:var(--muted);">To</label>
            <input type="date" name="to" class="param-input" value="{{ $to }}">
        </div>
        <button type="submit" class="btn btn-saffron">
            <i class="fas fa-chart-bar"></i> Generate Report
        </button>
    </form>
</div>

{{-- Summary Stats --}}
<div class="stats-grid" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-label">Total Revenue</div>
        <div class="stat-value">&#8377;{{ number_format($summary['total_revenue']) }}</div>
        <div class="stat-sub">{{ $from }} to {{ $to }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Bookings</div>
        <div class="stat-value">{{ number_format($summary['total_bookings']) }}</div>
        <div class="stat-sub">Paid only</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Visitors</div>
        <div class="stat-value">{{ number_format($summary['total_visitors']) }}</div>
        <div class="stat-sub">Tickets issued</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Avg. Booking Value</div>
        <div class="stat-value">
            &#8377;{{ $summary['total_bookings'] > 0 ? number_format($summary['total_revenue'] / $summary['total_bookings']) : 0 }}
        </div>
        <div class="stat-sub">Per booking</div>
    </div>
</div>

{{-- Daily + Top Slots --}}
<div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:24px;">

    {{-- Daily Revenue Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-calendar-alt"></i> Daily Breakdown</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="param-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Bookings</th>
                        <th>Revenue</th>
                        <th>Bar</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyRevenue as $day)
                    @php $maxRev = $dailyRevenue->max('revenue') ?: 1; $barPct = round(($day->revenue / $maxRev) * 100); @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($day->date)->format('D, d M') }}</td>
                        <td>{{ $day->bookings }}</td>
                        <td class="text-saffron fw-bold">&#8377;{{ number_format($day->revenue) }}</td>
                        <td style="min-width:100px;">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:{{ $barPct }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-muted text-sm text-center" style="padding:20px;">No data for this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Top Slots --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-trophy"></i> Top Slots by Revenue</span>
        </div>
        @forelse($topSlots as $slot)
        <div style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                <span style="font-size:13px;font-weight:600;color:var(--off-white);">{{ $slot->label }}</span>
                <span class="text-saffron fw-bold text-sm">&#8377;{{ number_format($slot->revenue) }}</span>
            </div>
            <div class="text-sm text-muted">{{ $slot->booking_count }} bookings</div>
        </div>
        @empty
        <p class="text-muted text-sm">No data yet.</p>
        @endforelse
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- UTM SOURCE ANALYTICS ─────────────────────────────────────────── --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px;">

    {{-- Source Breakdown Table --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-bullseye"></i> Traffic Source Breakdown</span>
            <span class="badge badge-gold text-sm">UTM</span>
        </div>
        @if($sourceStats->isEmpty())
            <p class="text-muted text-sm">No booking data for this range.</p>
        @else
        <table class="param-table">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Bookings</th>
                    <th>Revenue</th>
                    <th>Share</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sourceStats as $row)
                @php
                    $src  = strtolower($row->source ?? 'direct');
                    $icon = match($src) {
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
                <tr>
                    <td>
                        <span style="display:flex;align-items:center;gap:8px;">
                            <i class="{{ $icon }}" style="color:{{ $color }};font-size:16px;width:18px;text-align:center;"></i>
                            <span class="fw-bold" style="text-transform:capitalize;">{{ $row->source }}</span>
                        </span>
                    </td>
                    <td>{{ $row->bookings }}</td>
                    <td class="text-saffron fw-bold">&#8377;{{ number_format($row->revenue) }}</td>
                    <td style="min-width:100px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="progress-bar" style="flex:1;">
                                <div class="progress-fill" style="width:{{ $row->pct }}%; background:{{ $color }};"></div>
                            </div>
                            <span class="text-sm text-muted">{{ $row->pct }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Campaign Breakdown --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-tags"></i> Campaign Performance</span>
            <span class="badge badge-gold text-sm">UTM</span>
        </div>
        @if($campaignStats->isEmpty())
            <div style="text-align:center;padding:32px 0;color:var(--muted);">
                <i class="fas fa-tag" style="font-size:28px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                <div class="text-sm">No campaign data yet.</div>
                <div class="text-sm" style="margin-top:6px;">
                    Add <code style="background:var(--navy-light);padding:2px 6px;border-radius:4px;">?utm_campaign=your-campaign</code> to your links.
                </div>
            </div>
        @else
        <table class="param-table">
            <thead>
                <tr>
                    <th>Campaign</th>
                    <th>Source / Medium</th>
                    <th>Bookings</th>
                    <th>Revenue</th>
                </tr>
            </thead>
            <tbody>
                @foreach($campaignStats as $row)
                <tr>
                    <td>
                        <span style="font-size:11px;background:var(--navy-light);padding:2px 7px;border-radius:4px;color:var(--off-white);font-weight:600;">
                            {{ $row->campaign }}
                        </span>
                    </td>
                    <td class="text-sm text-muted">
                        {{ $row->source }} / {{ $row->medium }}
                    </td>
                    <td>{{ $row->bookings }}</td>
                    <td class="text-saffron fw-bold">&#8377;{{ number_format($row->revenue) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- UTM How-to Guide --}}
<div class="card" style="background:rgba(232,119,34,0.05); border:1px solid rgba(232,119,34,0.2);">
    <div class="card-header">
        <span class="card-title"><i class="fas fa-info-circle" style="color:var(--saffron);"></i> How to Track Campaign Links</span>
    </div>
    <div style="font-size:13px; color:var(--muted); line-height:1.8;">
        <p style="margin-bottom:12px;">Add UTM parameters to any link you share. Examples:</p>
        <div style="display:grid;gap:8px;">
            <div style="background:var(--navy-light);padding:10px 14px;border-radius:6px;font-family:monospace;font-size:12px;color:var(--off-white);">
                <span style="color:var(--muted);">Google Ads:</span><br>
                https://tickets.parsec.in?<span style="color:#4285F4;">utm_source=google</span>&<span style="color:#E87722;">utm_medium=cpc</span>&<span style="color:#25D366;">utm_campaign=summer-fest</span>
            </div>
            <div style="background:var(--navy-light);padding:10px 14px;border-radius:6px;font-family:monospace;font-size:12px;color:var(--off-white);">
                <span style="color:var(--muted);">Instagram Bio:</span><br>
                https://tickets.parsec.in?<span style="color:#E1306C;">utm_source=instagram</span>&<span style="color:#E87722;">utm_medium=social</span>&<span style="color:#25D366;">utm_campaign=bio-link</span>
            </div>
            <div style="background:var(--navy-light);padding:10px 14px;border-radius:6px;font-family:monospace;font-size:12px;color:var(--off-white);">
                <span style="color:var(--muted);">WhatsApp Broadcast:</span><br>
                https://tickets.parsec.in?<span style="color:#25D366;">utm_source=whatsapp</span>&<span style="color:#E87722;">utm_medium=broadcast</span>&<span style="color:#25D366;">utm_campaign=may-promo</span>
            </div>
        </div>
        <p style="margin-top:12px;">
            <i class="fas fa-lightbulb" style="color:var(--saffron);"></i>
            Direct visitors (no UTM) are automatically classified as <strong style="color:var(--off-white);">direct</strong>.
            Referrals from Google without UTM are auto-classified as <strong style="color:var(--off-white);">google</strong>.
        </p>
    </div>
</div>

@endsection
