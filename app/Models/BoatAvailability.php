<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoatAvailability extends Model
{
    use HasFactory;

    protected $table = 'boat_availability';

    protected $fillable = [
        'boat_id',
        'date',
        'is_available',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
        'is_available' => 'boolean',
    ];

    public function boat()
    {
        return $this->belongsTo(Boat::class);
    }
}
