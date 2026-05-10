<?php
namespace App\Jobs;

use App\Models\Booking;
use App\Models\Ticket;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTicketWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Number of times to retry on failure */
    public int $tries = 3;

    public function __construct(
        public Booking $booking,
        public array   $tickets,
    ) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $ticketCodes = collect($this->tickets)
            ->pluck('ticket_code')
            ->implode(', ');

        $whatsapp->sendTicket(
            phone:      $this->booking->phone,
            name:       $this->booking->name,
            ticketCode: $ticketCodes,
            slotLabel:  $this->booking->slot->label . ' on ' . $this->booking->slot->date->format('d M Y'),
        );
    }
}
