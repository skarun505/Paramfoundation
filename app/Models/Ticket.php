<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    protected $fillable = ['booking_id', 'ticket_code', 'qr_code_path', 'status'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function checkIn(): HasOne
    {
        return $this->hasOne(CheckIn::class);
    }
}
