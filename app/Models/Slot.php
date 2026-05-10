<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    protected $fillable = [
        'label', 'start_time', 'end_time', 'date',
        'capacity', 'booked', 'price', 'is_active',
    ];

    protected $casts = [
        'date'      => 'date',
        'is_active' => 'boolean',
        'price'     => 'decimal:2',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** Seats still available */
    public function getAvailableAttribute(): int
    {
        return max(0, $this->capacity - $this->booked);
    }

    /** Is the slot completely full? */
    public function getIsFullAttribute(): bool
    {
        return $this->booked >= $this->capacity;
    }
}
