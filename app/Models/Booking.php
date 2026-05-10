<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'slot_id', 'name', 'email', 'phone',
        'quantity', 'total_amount', 'status',
        'razorpay_order_id', 'razorpay_payment_id',
        // UTM tracking
        'utm_source', 'utm_medium', 'utm_campaign',
        'utm_content', 'utm_term',
        'referrer', 'landing_page',
    ];

    /**
     * Resolve a human-readable channel label from utm_source.
     */
    public function getChannelLabelAttribute(): string
    {
        return match (strtolower($this->utm_source ?? 'direct')) {
            'google'       => '🔍 Google',
            'facebook'     => '📘 Facebook',
            'instagram'    => '📸 Instagram',
            'twitter'      => '🐦 Twitter / X',
            'linkedin'     => '💼 LinkedIn',
            'youtube'      => '▶️ YouTube',
            'whatsapp'     => '💬 WhatsApp',
            'bing'         => '🔍 Bing',
            'duckduckgo'   => '🦆 DuckDuckGo',
            'referral'     => '🔗 Referral',
            'internal'     => '🏠 Internal',
            'direct'       => '🎯 Direct',
            default        => ucfirst($this->utm_source ?? 'Direct'),
        };
    }

    /**
     * Badge color class for a given source (used in views).
     */
    public function getChannelBadgeAttribute(): string
    {
        return match (strtolower($this->utm_source ?? 'direct')) {
            'google'    => 'badge-blue',
            'facebook',
            'instagram' => 'badge-purple',
            'whatsapp'  => 'badge-green',
            'direct'    => 'badge-gold',
            default     => 'badge-gray',
        };
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
