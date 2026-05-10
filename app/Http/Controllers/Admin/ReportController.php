<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();
        $range = [$from, $to . ' 23:59:59'];

        // ── Summary stats ─────────────────────────────────────────────
        $paidBase = Booking::whereBetween('created_at', $range)->where('status', 'paid');

        $summary = [
            'total_revenue'  => (clone $paidBase)->sum('total_amount'),
            'total_bookings' => (clone $paidBase)->count(),
            'total_visitors' => (clone $paidBase)->sum('quantity'),
        ];

        // ── Daily revenue breakdown ───────────────────────────────────
        $dailyRevenue = Booking::selectRaw(
                'DATE(created_at) as date,
                 SUM(total_amount) as revenue,
                 COUNT(*) as bookings'
            )
            ->whereBetween('created_at', $range)
            ->where('status', 'paid')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ── Top slots by revenue ──────────────────────────────────────
        $topSlots = Slot::withSum(
                ['bookings as revenue' => fn($q) => $q->where('status','paid')->whereBetween('created_at', $range)],
                'total_amount'
            )
            ->withCount(
                ['bookings as booking_count' => fn($q) => $q->where('status','paid')->whereBetween('created_at', $range)]
            )
            ->orderByDesc('revenue')
            ->take(5)
            ->get();

        // ── UTM Source breakdown ───────────────────────────────────────
        $sourceStats = Booking::selectRaw(
                'COALESCE(NULLIF(utm_source, ""), "direct") as source,
                 COUNT(*) as bookings,
                 SUM(total_amount) as revenue,
                 SUM(quantity) as visitors'
            )
            ->whereBetween('created_at', $range)
            ->where('status', 'paid')
            ->groupBy('source')
            ->orderByDesc('revenue')
            ->get();

        // ── UTM Campaign breakdown ────────────────────────────────────
        $campaignStats = Booking::selectRaw(
                'COALESCE(NULLIF(utm_campaign, ""), "(none)") as campaign,
                 COALESCE(NULLIF(utm_source, ""), "direct") as source,
                 COALESCE(NULLIF(utm_medium, ""), "(none)") as medium,
                 COUNT(*) as bookings,
                 SUM(total_amount) as revenue'
            )
            ->whereBetween('created_at', $range)
            ->where('status', 'paid')
            ->whereNotNull('utm_campaign')
            ->where('utm_campaign', '!=', '')
            ->groupBy('campaign', 'source', 'medium')
            ->orderByDesc('revenue')
            ->take(10)
            ->get();

        // ── Source → revenue percentage (for donut chart) ────────────
        $totalRevenue = $summary['total_revenue'] ?: 1;
        $sourceStats = $sourceStats->map(function ($row) use ($totalRevenue) {
            $row->pct = round(($row->revenue / $totalRevenue) * 100, 1);
            return $row;
        });

        return view('admin.reports.index', compact(
            'summary', 'dailyRevenue', 'topSlots',
            'sourceStats', 'campaignStats',
            'from', 'to'
        ));
    }
}
