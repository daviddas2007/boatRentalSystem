<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Boat;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $totalRevenue = Payment::where('status', 'succeeded')->sum('amount');
        $totalBookings = Booking::count();
        $activeRentals = Booking::whereIn('status', ['confirmed'])->count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalBoats = Boat::count();

        $pendingBookings = Booking::where('status', 'pending')->count();
        $completedBookings = Booking::where('status', 'completed')->count();
        $cancelledBookings = Booking::where('status', 'cancelled')->count();

        return response()->json([
            'total_revenue' => round($totalRevenue, 2),
            'total_bookings' => $totalBookings,
            'active_rentals' => $activeRentals,
            'total_customers' => $totalCustomers,
            'total_boats' => $totalBoats,
            'pending_bookings' => $pendingBookings,
            'completed_bookings' => $completedBookings,
            'cancelled_bookings' => $cancelledBookings,
        ]);
    }

    public function popularBoats(): JsonResponse
    {
        $popularBoats = Boat::withCount('bookings')
            ->orderByDesc('bookings_count')
            ->limit(5)
            ->get(['id', 'name', 'type', 'featured_image', 'location', 'price_per_hour']);

        return response()->json($popularBoats);
    }

    public function monthlyRevenue(Request $request): JsonResponse
    {
        $year = $request->input('year', Carbon::now()->year);

        $monthlyRevenue = Payment::where('status', 'succeeded')
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy('month')
            ->get();

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $found = $monthlyRevenue->firstWhere('month', $i);
            $months[] = [
                'month' => Carbon::create()->month($i)->format('M'),
                'month_number' => $i,
                'revenue' => $found ? round($found->revenue, 2) : 0,
                'count' => $found ? $found->count : 0,
            ];
        }

        return response()->json($months);
    }

    public function recentBookings(): JsonResponse
    {
        $bookings = Booking::with(['boat:id,name,type', 'user:id,name,email', 'payment'])
            ->latest()
            ->limit(10)
            ->get();

        return response()->json($bookings);
    }

    public function bookingsByStatus(): JsonResponse
    {
        $stats = Booking::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        return response()->json($stats);
    }

    public function revenueByBoatType(): JsonResponse
    {
        $revenue = Payment::where('status', 'succeeded')
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('boats', 'bookings.boat_id', '=', 'boats.id')
            ->select('boats.type', DB::raw('SUM(payments.amount) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('boats.type')
            ->get();

        return response()->json($revenue);
    }
}
