<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $booking = Booking::findOrFail($validated['booking_id']);

        if ($booking->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'You can only review completed bookings'], 422);
        }

        $existingReview = Review::where('user_id', $request->user()->id)
            ->where('booking_id', $booking->id)
            ->first();

        if ($existingReview) {
            return response()->json(['message' => 'You have already reviewed this booking'], 422);
        }

        $review = Review::create([
            'user_id' => $request->user()->id,
            'boat_id' => $booking->boat_id,
            'booking_id' => $booking->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($review->load('user:id,name'), 201);
    }

    public function boatReviews(int $boatId): JsonResponse
    {
        $reviews = Review::with('user:id,name')
            ->where('boat_id', $boatId)
            ->latest()
            ->paginate(10);

        return response()->json($reviews);
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        if ($review->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }
}
