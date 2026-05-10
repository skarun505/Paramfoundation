<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Slot;
use App\Models\CheckIn;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'today_bookings' => Booking::whereDate('created_at', today())
                ->where('status', 'paid')->count(),
            'today_revenue'  => Booking::whereDate('created_at', today())
                ->where('status', 'paid')->sum('total_amount'),
            'inside_now'     => CheckIn::whereNull('checked_out_at')->count(),
            'total_visitors' => CheckIn::count(),
        ];

        // Today's slots with occupancy
        $slots = Slot::whereDate('date', today())
            ->orderBy('start_time')
            ->get();

        // Recent bookings (last 10)
        $recentBookings = Booking::with('slot')
            ->where('status', 'paid')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'slots', 'recentBookings'));
    }
}
