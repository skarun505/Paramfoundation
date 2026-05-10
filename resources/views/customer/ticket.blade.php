@extends('layouts.app')
@section('title', 'Your Ticket')

@section('content')
<div class="ticket-container">
    <div class="ticket-header">
        <h1>Booking Confirmed!</h1>
        <p>Show this QR code at the ParSEC entry gate</p>
    </div>

    {{-- Booking Summary --}}
    <div class="card mb-24" style="margin-bottom:20px;">
        <div style="display:flex; gap:20px; flex-wrap:wrap; font-size:14px;">
            <div><div class="text-muted text-sm">Name</div><div class="fw-bold">{{ $booking->name }}</div></div>
            <div><div class="text-muted text-sm">Slot</div><div class="fw-bold">{{ $booking->slot->label }}</div></div>
            <div><div class="text-muted text-sm">Date</div><div class="fw-bold">{{ $booking->slot->date->format('D, d M Y') }}</div></div>
            <div><div class="text-muted text-sm">Visitors</div><div class="fw-bold">{{ $booking->quantity }}</div></div>
            <div><div class="text-muted text-sm">Amount Paid</div><div class="fw-bold text-saffron">&#8377;{{ number_format($booking->total_amount) }}</div></div>
        </div>
    </div>

    {{-- Individual Tickets --}}
    @foreach($booking->tickets as $index => $ticket)
    <div class="ticket-card">
        <div class="ticket-top">
            <div style="font-size:13px; opacity:0.85; margin-bottom:4px;">
                ParSEC Entry Ticket &bull; Visitor {{ $index + 1 }} of {{ $booking->quantity }}
            </div>
            <div class="ticket-code">{{ $ticket->ticket_code }}</div>
        </div>

        <hr class="ticket-divider">

        <div class="ticket-body">
            {{-- QR Code --}}
            <div class="qr-wrap">
                @php
                    $qrExists = !str_contains($ticket->qr_code_path, 'dummy')
                                && file_exists(storage_path('app/public/' . $ticket->qr_code_path));
                @endphp
                @if($qrExists)
                    <object data="{{ asset('storage/' . $ticket->qr_code_path) }}"
                            type="image/svg+xml"
                            width="160" height="160">
                        <img src="{{ asset('storage/' . $ticket->qr_code_path) }}" alt="QR Code" width="160" height="160">
                    </object>
                @else
                    {{-- Fallback: visual ticket code block --}}
                    <div style="width:160px;height:160px;background:#0D1B2A;border-radius:8px;
                                display:flex;flex-direction:column;align-items:center;
                                justify-content:center;border:2px dashed var(--saffron);padding:12px;
                                text-align:center;">
                        <div style="font-size:10px;color:var(--muted);margin-bottom:8px;letter-spacing:0.1em;">TICKET CODE</div>
                        <div style="font-family:monospace;font-size:13px;font-weight:700;
                                    color:var(--saffron);word-break:break-all;line-height:1.4;">
                            {{ $ticket->ticket_code }}
                        </div>
                        <div style="font-size:9px;color:var(--muted);margin-top:8px;">
                            Show at entry gate
                        </div>
                    </div>
                @endif
            </div>

            {{-- Ticket Details --}}
            <div class="ticket-details">
                <div class="ticket-detail-row">
                    <div class="label">Visitor Name</div>
                    <div class="value">{{ $booking->name }}</div>
                </div>
                <div class="ticket-detail-row">
                    <div class="label">Time Slot</div>
                    <div class="value">{{ $booking->slot->label }}</div>
                </div>
                <div class="ticket-detail-row">
                    <div class="label">Date</div>
                    <div class="value">{{ $booking->slot->date->format('D, d M Y') }}</div>
                </div>
                <div class="ticket-detail-row">
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="badge badge-green">{{ ucfirst($ticket->status) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="ticket-footer">
            <span><i class="fas fa-atom"></i> ParSEC &middot; Param Foundation, Bengaluru</span>
            <span class="text-sm">Valid for 1 entry only</span>
        </div>
    </div>
    @endforeach

    {{-- Actions --}}
    <div class="d-flex gap-12 flex-wrap" style="margin-top:24px;">
        <button onclick="window.print()" class="btn btn-outline">
            <i class="fas fa-print"></i> Print Tickets
        </button>
        <a href="{{ route('customer.slots') }}" class="btn btn-ghost">
            <i class="fas fa-calendar-plus"></i> Book Another Date
        </a>
    </div>

    <div class="card" style="margin-top:24px; font-size:13px; color:var(--muted);">
        <strong style="color:var(--off-white);">Important Instructions:</strong>
        <ul style="margin-top:8px; padding-left:18px; line-height:1.8;">
            <li>Present this QR code at the ParSEC entry gate.</li>
            <li>Arrive within your booked time slot. Entry after the slot ends may be denied.</li>
            <li>Each QR code is valid for one person only.</li>
            <li>Tickets are non-transferable and non-refundable.</li>
        </ul>
    </div>
</div>
@endsection
