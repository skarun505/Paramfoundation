<?php
namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\CheckIn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    /**
     * Show PIN login form.
     */
    public function loginForm()
    {
        return view('scanner.login');
    }

    /**
     * Authenticate scanner staff with PIN.
     */
    public function login(Request $request)
    {
        $request->validate(['pin' => 'required|digits:4']);

        if ($request->pin !== config('app.scanner_pin', '1234')) {
            return back()->withErrors(['pin' => 'Incorrect PIN. Please try again.']);
        }

        session(['scanner_authenticated' => true]);
        return redirect()->route('scanner.index');
    }

    /**
     * PWA scanner page.
     */
    public function index()
    {
        return view('scanner.scan');
    }

    /**
     * Verify a ticket code (called by scanner after reading QR).
     */
    public function verify(string $code): JsonResponse
    {
        $ticket = Ticket::where('ticket_code', $code)
                        ->with(['booking.slot', 'checkIn'])
                        ->first();

        if (!$ticket) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid ticket.',
            ], 404);
        }

        if ($ticket->status === 'used') {
            return response()->json([
                'status'        => 'already_used',
                'message'       => 'Ticket already scanned.',
                'checked_in_at' => optional($ticket->checkIn)->checked_in_at?->format('h:i A'),
            ]);
        }

        if ($ticket->status === 'expired') {
            return response()->json([
                'status'  => 'expired',
                'message' => 'Ticket has expired.',
            ]);
        }

        // Valid — mark entry
        CheckIn::create([
            'ticket_id'     => $ticket->id,
            'checked_in_at' => now(),
            'scanned_by'    => 'Scanner Station',
        ]);
        $ticket->update(['status' => 'used']);

        return response()->json([
            'status'  => 'success',
            'name'    => $ticket->booking->name,
            'slot'    => $ticket->booking->slot->label,
            'message' => 'Entry granted! Welcome to ParSEC.',
        ]);
    }

    /**
     * Live occupancy count (polled every 30s by scanner dashboard).
     */
    public function liveCount(): JsonResponse
    {
        $inside = CheckIn::whereNull('checked_out_at')->count();

        return response()->json([
            'inside'     => $inside,
            'updated_at' => now()->format('h:i A'),
        ]);
    }

    /**
     * Logout scanner staff.
     */
    public function logout(Request $request)
    {
        $request->session()->forget('scanner_authenticated');
        return redirect()->route('scanner.login');
    }
}
