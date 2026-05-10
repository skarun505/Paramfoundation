<?php
namespace App\Services;

use Razorpay\Api\Api;
use Illuminate\Support\Facades\Log;

class RazorpayService
{
    protected Api $api;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
    }

    /**
     * Create a Razorpay order.
     * @return array  Razorpay order object as array
     */
    public function createOrder(float $amount, string $receipt): array
    {
        $order = $this->api->order->create([
            'receipt'  => $receipt,
            'amount'   => (int) round($amount * 100), // Convert to paise
            'currency' => 'INR',
        ]);

        return $order->toArray();
    }

    /**
     * Verify Razorpay payment signature (HMAC-SHA256).
     */
    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool
    {
        $expectedSig = hash_hmac(
            'sha256',
            $orderId . '|' . $paymentId,
            config('services.razorpay.secret')
        );

        return hash_equals($expectedSig, $signature);
    }
}
