<?php
namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QRCodeService
{
    /**
     * Generate an SVG QR code for a ticket code.
     * Saves to storage/app/public/qrcodes/{ticketCode}.svg
     *
     * @return string  Storage-relative path (usable with asset('storage/...'))
     */
    public function generate(string $ticketCode): string
    {
        $filename = 'qrcodes/' . $ticketCode . '.svg';
        $path     = storage_path('app/public/' . $filename);

        // Ensure the qrcodes directory exists
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        $verifyUrl = url('/scanner/verify/' . $ticketCode);

        QrCode::format('svg')
              ->size(300)
              ->errorCorrection('H')
              ->generate($verifyUrl, $path);

        return $filename;
    }

    /**
     * Get the public URL for a QR code.
     */
    public function url(string $filename): string
    {
        return asset('storage/' . $filename);
    }
}
