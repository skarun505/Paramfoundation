<?php
namespace App\Jobs;

use App\Models\Booking;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public Booking $booking) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $whatsapp->sendReminder(
            phone:     $this->booking->phone,
            name:      $this->booking->name,
            slotLabel: $this->booking->slot->label,
            slotDate:  $this->booking->slot->date->format('d M Y'),
        );
    }
}
