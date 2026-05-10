<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    /**
     * Booking detail — UTM, payment, customer lifetime stats, tickets.
     */
    public function show(Booking $booking)
    {
        $booking->load(['slot', 'tickets']);

        // All bookings from same phone (customer history)
        $customerBookings = Booking::with('slot')
            ->where('phone', $booking->phone)
            ->latest()
            ->get();

        $paidHistory = $customerBookings->where('status', 'paid');

        $customerStats = [
            'total_bookings'   => $customerBookings->count(),
            'paid_bookings'    => $paidHistory->count(),
            'total_revenue'    => $paidHistory->sum('total_amount'),
            'total_visitors'   => $paidHistory->sum('quantity'),
            'first_booking_at' => $customerBookings->last()?->created_at,
            'is_returning'     => $customerBookings->count() > 1,
        ];

        return view('admin.bookings.show', compact('booking', 'customerBookings', 'customerStats'));
    }

    public function index(Request $request)
    {
        $query = Booking::with('slot')->latest();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name',  'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('razorpay_payment_id', 'like', "%{$s}%")
                  ->orWhere('utm_campaign', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereHas('slot', fn($q) => $q->whereDate('date', $request->date));
        }

        // ── UTM Source filter ──────────────────────────────────────────
        if ($request->filled('source')) {
            $query->where('utm_source', $request->source);
        }

        $bookings = $query->paginate(25)->withQueryString();

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Export bookings to CSV (includes UTM tracking columns).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = Booking::with('slot')->where('status', 'paid');

        if ($request->filled('date')) {
            $query->whereHas('slot', fn($q) => $q->whereDate('date', $request->date));
        }
        if ($request->filled('source')) {
            $query->where('utm_source', $request->source);
        }

        $bookings = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="parsec-bookings-' . now()->format('Ymd-His') . '.csv"',
        ];

        return response()->stream(function () use ($bookings) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'ID', 'Name', 'Email', 'Phone',
                'Slot', 'Date', 'Qty', 'Amount', 'Payment ID',
                'Source', 'Medium', 'Campaign', 'Content', 'Term',
                'Referrer', 'Landing Page', 'Booked At',
            ]);
            foreach ($bookings as $b) {
                fputcsv($handle, [
                    $b->id, $b->name, $b->email, $b->phone,
                    $b->slot?->label, $b->slot?->date?->format('d M Y'),
                    $b->quantity, $b->total_amount, $b->razorpay_payment_id,
                    $b->utm_source   ?? 'direct',
                    $b->utm_medium   ?? '',
                    $b->utm_campaign ?? '',
                    $b->utm_content  ?? '',
                    $b->utm_term     ?? '',
                    $b->referrer     ?? '',
                    $b->landing_page ?? '',
                    $b->created_at->format('d M Y h:i A'),
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
