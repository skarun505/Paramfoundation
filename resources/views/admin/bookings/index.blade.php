@extends('layouts.admin')
@section('title', 'Bookings')

@section('content')

{{-- Filters --}}
<div class="card" style="margin-bottom:20px;">
    <form method="GET" action="{{ route('admin.bookings.index') }}"
          style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="flex:1; min-width:180px; margin-bottom:0;">
            <label style="font-size:12px;color:var(--muted);">Search</label>
            <input type="text" name="search" class="param-input"
                   placeholder="Name, email, phone, payment ID..."
                   value="{{ request('search') }}">
        </div>
        <div class="form-group" style="min-width:130px; margin-bottom:0;">
            <label style="font-size:12px;color:var(--muted);">Status</label>
            <select name="status" class="param-input">
                <option value="">All Statuses</option>
                <option value="paid"      {{ request('status')=='paid'      ?'selected':'' }}>Paid</option>
                <option value="pending"   {{ request('status')=='pending'   ?'selected':'' }}>Pending</option>
                <option value="cancelled" {{ request('status')=='cancelled' ?'selected':'' }}>Cancelled</option>
            </select>
        </div>
        <div class="form-group" style="min-width:130px; margin-bottom:0;">
            <label style="font-size:12px;color:var(--muted);">Source</label>
            <select name="source" class="param-input">
                <option value="">All Sources</option>
                @foreach(['direct','google','facebook','instagram','whatsapp','twitter','linkedin','youtube','referral'] as $src)
                    <option value="{{ $src }}" {{ request('source')==$src ?'selected':'' }}>
                        {{ ucfirst($src) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group" style="min-width:150px; margin-bottom:0;">
            <label style="font-size:12px;color:var(--muted);">Slot Date</label>
            <input type="date" name="date" class="param-input" value="{{ request('date') }}">
        </div>
        <button type="submit" class="btn btn-saffron">
            <i class="fas fa-filter"></i> Filter
        </button>
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-outline">Reset</a>
        <a href="{{ route('admin.bookings.export') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}"
           class="btn btn-outline">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
    </form>
</div>

{{-- Bookings Table --}}
<div class="card">
    <div style="overflow-x:auto;">
        <table class="param-table" id="bookings-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Slot</th>
                    <th>Date</th>
                    <th>Qty</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th><i class="fas fa-bullseye" style="color:var(--saffron);"></i> Source</th>
                    <th>Campaign</th>
                    <th>Booked At</th>
                    <th style="width:40px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $b)
                <tr onclick="window.location='{{ route('admin.bookings.show', $b->id) }}'"
                    style="cursor:pointer; transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(232,119,34,0.07)'"
                    onmouseout="this.style.background=''">
                    <td class="text-muted text-sm">{{ $b->id }}</td>
                    <td>
                        <div class="fw-bold">{{ $b->name }}</div>
                        <div class="text-sm text-muted">{{ $b->email }}</div>
                        <div class="text-sm text-muted">{{ $b->phone }}</div>
                    </td>
                    <td class="text-sm">{{ $b->slot?->label }}</td>
                    <td class="text-sm">{{ $b->slot?->date?->format('d M Y') }}</td>
                    <td>{{ $b->quantity }}</td>
                    <td class="text-saffron fw-bold">&#8377;{{ number_format($b->total_amount) }}</td>
                    <td>
                        @if($b->status === 'paid')
                            <span class="badge badge-green">Paid</span>
                        @elseif($b->status === 'pending')
                            <span class="badge badge-gold">Pending</span>
                        @else
                            <span class="badge badge-red">Cancelled</span>
                        @endif
                    </td>

                    {{-- UTM Source --}}
                    <td>
                        @php
                            $src = strtolower($b->utm_source ?? 'direct');
                            $icon = match($src) {
                                'google'    => 'fab fa-google',
                                'facebook'  => 'fab fa-facebook',
                                'instagram' => 'fab fa-instagram',
                                'whatsapp'  => 'fab fa-whatsapp',
                                'twitter'   => 'fab fa-twitter',
                                'linkedin'  => 'fab fa-linkedin',
                                'youtube'   => 'fab fa-youtube',
                                'bing'      => 'fas fa-search',
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
                                'direct'    => 'var(--saffron)',
                                default     => 'var(--muted)',
                            };
                        @endphp
                        <span style="display:flex;align-items:center;gap:5px;white-space:nowrap;">
                            <i class="{{ $icon }}" style="color:{{ $color }};font-size:14px;"></i>
                            <span class="text-sm">{{ ucfirst($b->utm_source ?? 'Direct') }}</span>
                        </span>
                        @if($b->utm_medium)
                            <div class="text-sm text-muted">{{ $b->utm_medium }}</div>
                        @endif
                    </td>

                    {{-- Campaign --}}
                    <td class="text-sm text-muted">
                        @if($b->utm_campaign)
                            <span style="font-size:11px; background:var(--navy-light);
                                         padding:2px 6px; border-radius:4px; color:var(--off-white);">
                                {{ $b->utm_campaign }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td class="text-sm text-muted">{{ $b->created_at->format('d M, h:i A') }}</td>
                    <td style="text-align:center;">
                        <i class="fas fa-chevron-right" style="color:var(--muted); font-size:11px;"></i>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted" style="padding:40px;">
                        <i class="fas fa-inbox" style="font-size:32px;display:block;margin-bottom:12px;"></i>
                        No bookings found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($bookings->hasPages())
    <div class="pagination" style="margin-top:20px;padding-top:16px;border-top:1px solid var(--navy-light);">
        {{ $bookings->links() }}
    </div>
    @endif
</div>
@endsection
