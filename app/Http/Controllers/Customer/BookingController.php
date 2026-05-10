<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use App\Models\Booking;
use App\Models\Ticket;
use App\Services\QRCodeService;
use App\Jobs\SendTicketWhatsApp;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * DUMMY MODE: Razorpay is skipped when credentials are not configured.
     * Set RAZORPAY_KEY to a real key to enable live payments.
     */
    private function isDummyMode(): bool
    {
        $key = config('services.razorpay.key', '');
        return empty($key) || str_contains($key, 'xxxx') || str_contains($key, 'test_xxx');
    }

    /**
     * Step 1 — Show available slots for a given date.
     */
    public function slots(Request $request)
    {
        $date  = $request->date ?? now()->toDateString();
        $slots = Slot::whereDate('date', $date)
                     ->where('is_active', true)
                     ->orderBy('start_time')
                     ->get();

        return view('customer.slots', compact('slots', 'date'));
    }

    /**
     * Step 2 — Show checkout form.
     * In dummy mode, no Razorpay order is created.
     */
    public function checkout(Slot $slot)
    {
        abort_if($slot->is_full, 403, 'This slot is fully booked.');

        $dummyMode = $this->isDummyMode();

        // Only hit Razorpay if we have real credentials
        $order = null;
        if (!$dummyMode) {
            try {
                $razorpay = app(\App\Services\RazorpayService::class);
                $order    = $razorpay->createOrder($slot->price, 'PSC-' . uniqid());
            } catch (\Exception $e) {
                // Fall back to dummy mode if Razorpay fails
                $dummyMode = true;
            }
        }

        return view('customer.checkout', compact('slot', 'order', 'dummyMode'));
    }

    /**
     * Step 3 — Confirm booking.
     * In dummy mode, skips signature verification.
     */
    public function confirm(Request $request)
    {
        $dummyMode = $this->isDummyMode() || $request->boolean('dummy_mode');

        // Validation — loosen Razorpay fields in dummy mode
        $rules = [
            'slot_id'  => 'required|exists:slots,id',
            'name'     => 'required|string|max:100',
            'email'    => 'required|email',
            'phone'    => 'required|digits:10',
            'quantity' => 'required|integer|min:1|max:10',
        ];

        if (!$dummyMode) {
            $rules['razorpay_order_id']   = 'required|string';
            $rules['razorpay_payment_id'] = 'required|string';
            $rules['razorpay_signature']  = 'required|string';
        }

        $validated = $request->validate($rules);

        $slot = Slot::findOrFail($validated['slot_id']);
        abort_if($slot->available < $validated['quantity'], 422, 'Not enough seats available.');

        // Verify Razorpay signature only in live mode
        if (!$dummyMode) {
            $razorpay = app(\App\Services\RazorpayService::class);
            $valid = $razorpay->verifyPayment(
                $validated['razorpay_order_id'],
                $validated['razorpay_payment_id'],
                $validated['razorpay_signature']
            );
            abort_unless($valid, 400, 'Payment verification failed.');
        }

        // ── Pull UTM tracking data from session ───────────────────────
        $utm = $request->session()->get('utm', []);

        $booking = Booking::create([
            'slot_id'             => $slot->id,
            'name'                => $validated['name'],
            'email'               => $validated['email'],
            'phone'               => $validated['phone'],
            'quantity'            => $validated['quantity'],
            'total_amount'        => $slot->price * $validated['quantity'],
            'status'              => 'paid',
            'razorpay_order_id'   => $validated['razorpay_order_id']   ?? 'DUMMY-' . strtoupper(Str::random(8)),
            'razorpay_payment_id' => $validated['razorpay_payment_id'] ?? 'DUMMY-PAY-' . strtoupper(Str::random(8)),
            // UTM fields
            'utm_source'   => $utm['utm_source']   ?? 'direct',
            'utm_medium'   => $utm['utm_medium']   ?? null,
            'utm_campaign' => $utm['utm_campaign'] ?? null,
            'utm_content'  => $utm['utm_content']  ?? null,
            'utm_term'     => $utm['utm_term']      ?? null,
            'referrer'     => $utm['referrer']      ?? null,
            'landing_page' => $utm['landing_page']  ?? null,
        ]);

        // Generate one ticket per person — QR generation is optional
        $tickets = [];
        for ($i = 0; $i < $validated['quantity']; $i++) {
            $code = 'PSC-' . strtoupper(Str::random(8));

            // Try to generate QR — silently skip if ext-gd is missing
            $qrPath = 'qrcodes/dummy.svg'; // fallback
            try {
                $qr     = app(QRCodeService::class);
                $qrPath = $qr->generate($code);
            } catch (\Exception $e) {
                // QR generation unavailable — ticket still works via code
            }

            $tickets[] = Ticket::create([
                'booking_id'   => $booking->id,
                'ticket_code'  => $code,
                'qr_code_path' => $qrPath,
            ]);
        }

        // Update slot booked count
        $slot->increment('booked', $validated['quantity']);

        // Dispatch WhatsApp job (will log if no credentials)
        try {
            SendTicketWhatsApp::dispatch($booking, $tickets)->onQueue('notifications');
        } catch (\Exception $e) {
            // Non-critical — continue
        }

        return redirect()->route('customer.ticket', $booking->id)
            ->with('success', 'Booking confirmed! Your ticket is ready.');
    }
}
