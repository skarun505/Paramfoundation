<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    protected $fillable = [
        'ticket_id', 'checked_in_at', 'checked_out_at', 'scanned_by',
    ];

    protected $casts = [
        'checked_in_at'  => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
