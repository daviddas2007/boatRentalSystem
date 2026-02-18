<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Boat;
use App\Models\BoatAvailability;
use App\Models\BoatImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoatController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Boat::with(['images', 'owner:id,name'])
            ->where('is_active', true);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('min_price')) {
            $query->where('price_per_hour', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price_per_hour', '<=', $request->max_price);
        }

        if ($request->filled('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSorts = ['price_per_hour', 'price_per_day', 'capacity', 'created_at', 'name'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $boats = $query->paginate($request->input('per_page', 12));

        return response()->json($boats);
    }

    public function show(string $slug): JsonResponse
    {
        $boat = Boat::with(['images', 'owner:id,name', 'reviews.user:id,name'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json($boat);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:sailboat,yacht,speedboat,kayak,pontoon,catamaran',
            'description' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'price_per_hour' => 'required|numeric|min:0',
            'price_per_day' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'amenities' => 'nullable|array',
            'year_built' => 'nullable|integer|min:1900|max:' . date('Y'),
            'manufacturer' => 'nullable|string|max:255',
            'length_ft' => 'nullable|numeric|min:0',
            'featured_image' => 'nullable|image|max:5120',
        ]);

        $validated['owner_id'] = $request->user()->id;

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('boats', 'public');
            $validated['featured_image'] = $path;
        }

        $boat = Boat::create($validated);

        return response()->json($boat->load('images'), 201);
    }

    public function update(Request $request, Boat $boat): JsonResponse
    {
        if ($boat->owner_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:sailboat,yacht,speedboat,kayak,pontoon,catamaran',
            'description' => 'nullable|string',
            'capacity' => 'sometimes|integer|min:1',
            'price_per_hour' => 'sometimes|numeric|min:0',
            'price_per_day' => 'sometimes|numeric|min:0',
            'location' => 'sometimes|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_active' => 'sometimes|boolean',
            'amenities' => 'nullable|array',
            'year_built' => 'nullable|integer|min:1900|max:' . date('Y'),
            'manufacturer' => 'nullable|string|max:255',
            'length_ft' => 'nullable|numeric|min:0',
            'featured_image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('featured_image')) {
            $path = $request->file('featured_image')->store('boats', 'public');
            $validated['featured_image'] = $path;
        }

        $boat->update($validated);

        return response()->json($boat->load('images'));
    }

    public function destroy(Request $request, Boat $boat): JsonResponse
    {
        if ($boat->owner_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $boat->delete();

        return response()->json(['message' => 'Boat deleted successfully']);
    }

    public function uploadImages(Request $request, Boat $boat): JsonResponse
    {
        if ($boat->owner_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|max:5120',
        ]);

        $uploadedImages = [];
        foreach ($request->file('images') as $index => $image) {
            $path = $image->store('boats/gallery', 'public');
            $uploadedImages[] = BoatImage::create([
                'boat_id' => $boat->id,
                'image_path' => $path,
                'sort_order' => $boat->images()->count() + $index,
            ]);
        }

        return response()->json($uploadedImages, 201);
    }

    public function myBoats(Request $request): JsonResponse
    {
        $boats = Boat::with('images')
            ->where('owner_id', $request->user()->id)
            ->withCount('bookings')
            ->latest()
            ->paginate(10);

        return response()->json($boats);
    }

    public function showOwned(Request $request, Boat $boat): JsonResponse
    {
        if ($boat->owner_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($boat->load(['images', 'owner:id,name', 'reviews.user:id,name']));
    }

    public function getAvailability(Boat $boat, Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
        ]);

        $startDate = $request->month . '-01';
        $endDate = date('Y-m-t', strtotime($startDate));

        $availability = $boat->availability()
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $bookings = $boat->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->get(['start_date', 'end_date', 'status']);

        return response()->json([
            'availability' => $availability,
            'bookings' => $bookings,
        ]);
    }

    public function setAvailability(Request $request, Boat $boat): JsonResponse
    {
        if ($boat->owner_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'dates' => 'required|array',
            'dates.*.date' => 'required|date',
            'dates.*.is_available' => 'required|boolean',
            'dates.*.reason' => 'nullable|string',
        ]);

        foreach ($validated['dates'] as $dateData) {
            BoatAvailability::updateOrCreate(
                ['boat_id' => $boat->id, 'date' => $dateData['date']],
                ['is_available' => $dateData['is_available'], 'reason' => $dateData['reason'] ?? null]
            );
        }

        return response()->json(['message' => 'Availability updated successfully']);
    }

    public function types(): JsonResponse
    {
        return response()->json([
            'types' => ['sailboat', 'yacht', 'speedboat', 'kayak', 'pontoon', 'catamaran'],
        ]);
    }
}
