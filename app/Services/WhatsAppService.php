<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a ticket confirmation via WhatsApp.
     * Uses Meta-approved template: ticket_confirmation
     * Body values: [name, ticketCode, slotLabel]
     */
    public function sendTicket(string $phone, string $name, string $ticketCode, string $slotLabel): void
    {
        $token = config('services.whatsapp.token');
        $url   = config('services.whatsapp.url');

        // If credentials not configured, log instead of sending
        if (empty($token) || $token === 'your_whatsapp_token') {
            Log::info('[WhatsApp STUB] Ticket confirmation skipped — no credentials configured.', [
                'phone'      => $phone,
                'name'       => $name,
                'ticketCode' => $ticketCode,
                'slotLabel'  => $slotLabel,
            ]);
            return;
        }

        Http::withToken($token)
            ->post($url, [
                'countryCode' => '91',
                'phoneNumber' => $phone,
                'type'        => 'Template',
                'template'    => [
                    'name'       => 'ticket_confirmation',
                    'bodyValues' => [$name, $ticketCode, $slotLabel],
                ],
            ]);
    }

    /**
     * Send a reminder 2 hours before slot.
     */
    public function sendReminder(string $phone, string $name, string $slotLabel, string $slotDate): void
    {
        $token = config('services.whatsapp.token');
        $url   = config('services.whatsapp.url');

        if (empty($token) || $token === 'your_whatsapp_token') {
            Log::info('[WhatsApp STUB] Reminder skipped — no credentials configured.', compact('phone', 'name'));
            return;
        }

        Http::withToken($token)
            ->post($url, [
                'countryCode' => '91',
                'phoneNumber' => $phone,
                'type'        => 'Template',
                'template'    => [
                    'name'       => 'slot_reminder',
                    'bodyValues' => [$name, $slotLabel, $slotDate],
                ],
            ]);
    }
}
