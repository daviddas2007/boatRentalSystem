<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Boat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'type',
        'description',
        'capacity',
        'price_per_hour',
        'price_per_day',
        'location',
        'latitude',
        'longitude',
        'featured_image',
        'is_active',
        'amenities',
        'year_built',
        'manufacturer',
        'length_ft',
    ];

    protected $casts = [
        'amenities' => 'array',
        'is_active' => 'boolean',
        'price_per_hour' => 'decimal:2',
        'price_per_day' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'length_ft' => 'decimal:2',
    ];

    protected $appends = ['average_rating', 'review_count'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($boat) {
            if (empty($boat->slug)) {
                $boat->slug = Str::slug($boat->name) . '-' . Str::random(6);
            }
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function images()
    {
        return $this->hasMany(BoatImage::class)->orderBy('sort_order');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function availability()
    {
        return $this->hasMany(BoatAvailability::class);
    }

    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }

    public function isAvailableForPeriod($startDate, $endDate): bool
    {
        $hasConflictingBooking = $this->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<', $endDate)
                      ->where('end_date', '>', $startDate);
                });
            })
            ->exists();

        if ($hasConflictingBooking) {
            return false;
        }

        $hasBlockedDates = $this->availability()
            ->where('is_available', false)
            ->whereBetween('date', [$startDate, $endDate])
            ->exists();

        return !$hasBlockedDates;
    }
}
