<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;

class TicketController extends Controller
{
    /**
     * Show ticket(s) for a completed booking.
     */
    public function show(Booking $booking)
    {
        // Eager load tickets with QR paths and slot details
        $booking->load(['tickets', 'slot']);

        return view('customer.ticket', compact('booking'));
    }
}
