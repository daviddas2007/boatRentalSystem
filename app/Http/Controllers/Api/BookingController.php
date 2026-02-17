<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Boat;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $bookings = $request->user()->bookings()
            ->with(['boat:id,name,slug,type,featured_image,location', 'payment'])
            ->latest()
            ->paginate(10);

        return response()->json($bookings);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'boat_id' => 'required|exists:boats,id',
            'start_date' => 'required|date|after:now',
            'duration_type' => 'required|in:hourly,daily',
            'duration_value' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ]);

        $boat = Boat::findOrFail($validated['boat_id']);

        if (!$boat->is_active) {
            return response()->json(['message' => 'This boat is not available for booking'], 422);
        }

        $startDate = Carbon::parse($validated['start_date']);

        if ($validated['duration_type'] === 'hourly') {
            $endDate = $startDate->copy()->addHours($validated['duration_value']);
            $totalPrice = $boat->price_per_hour * $validated['duration_value'];
        } else {
            $endDate = $startDate->copy()->addDays($validated['duration_value']);
            $totalPrice = $boat->price_per_day * $validated['duration_value'];
        }

        if (!$boat->isAvailableForPeriod($startDate, $endDate)) {
            return response()->json([
                'message' => 'The boat is not available for the selected dates',
            ], 422);
        }

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'boat_id' => $boat->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_type' => $validated['duration_type'],
            'duration_value' => $validated['duration_value'],
            'total_price' => $totalPrice,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json($booking->load(['boat', 'payment']), 201);
    }

    public function show(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            $boat = Boat::find($booking->boat_id);
            if (!$boat || $boat->owner_id !== $request->user()->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json($booking->load(['boat.images', 'payment', 'review', 'user:id,name,email']));
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        if ($booking->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($booking->status, ['pending', 'confirmed'])) {
            return response()->json(['message' => 'This booking cannot be cancelled'], 422);
        }

        $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->cancellation_reason,
        ]);

        return response()->json($booking);
    }

    public function confirm(Request $request, Booking $booking): JsonResponse
    {
        $boat = Boat::find($booking->boat_id);
        if (!$boat || ($boat->owner_id !== $request->user()->id && !$request->user()->isAdmin())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'pending') {
            return response()->json(['message' => 'Only pending bookings can be confirmed'], 422);
        }

        $booking->update(['status' => 'confirmed']);

        return response()->json($booking);
    }

    public function complete(Request $request, Booking $booking): JsonResponse
    {
        $boat = Boat::find($booking->boat_id);
        if (!$boat || ($boat->owner_id !== $request->user()->id && !$request->user()->isAdmin())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'confirmed') {
            return response()->json(['message' => 'Only confirmed bookings can be completed'], 422);
        }

        $booking->update(['status' => 'completed']);

        return response()->json($booking);
    }

    public function adminIndex(Request $request): JsonResponse
    {
        $query = Booking::with(['boat:id,name,slug,type', 'user:id,name,email', 'payment']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('boat_id')) {
            $query->where('boat_id', $request->boat_id);
        }

        $bookings = $query->latest()->paginate(15);

        return response()->json($bookings);
    }
}
