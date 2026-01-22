<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SecurepayService
{
    public function uid(): string
    {
        return env('SECUREPAY_UID', '');
    }

    public function authToken(): string
    {
        return env('SECUREPAY_AUTH_TOKEN', '');
    }

    public function checksumToken(): string
    {
        return env('SECUREPAY_CHECKSUM_TOKEN', '');
    }

    public function url(): string
    {
        return rtrim(env('SECUREPAY_URL', 'https://securepay.my'), '/');
    }

    /**
     * Create a payment session/link.
     * Note: Adjust endpoints and payload according to specific API version if needed.
     */
    public function createPayment(array $data): array
    {
        $url = $this->url() . '/api/v1/payments';

        // Sample code logic:
        // $uid is NOT sent in POST body, only used for checksum.
        // $auth_token is sent as 'token'.
        
        // Generate Checksum
        // Pattern from sample.php: 
        // buyer_email|buyer_name|buyer_phone|callback_url|order_number|product_description|redirect_url|transaction_amount|uid
        
        $stringToSign = implode('|', [
            $data['buyer_email'] ?? '',
            $data['buyer_name'] ?? '',
            $data['buyer_phone'] ?? '',
            $data['callback_url'] ?? '',
            $data['order_number'] ?? '',
            $data['product_description'] ?? '',
            $data['redirect_url'] ?? '',
            $data['transaction_amount'] ?? '',
            $this->uid()
        ]);
        
        $checksum = hash_hmac('sha256', $stringToSign, $this->checksumToken());

        // Construct Payload strictly following sample.php
        $payload = [
            'buyer_name' => $data['buyer_name'] ?? '',
            'token' => $this->authToken(),
            'callback_url' => $data['callback_url'] ?? '',
            'redirect_url' => $data['redirect_url'] ?? '',
            'order_number' => $data['order_number'] ?? '',
            'buyer_email' => $data['buyer_email'] ?? '',
            'buyer_phone' => $data['buyer_phone'] ?? '',
            'transaction_amount' => $data['transaction_amount'] ?? '',
            'product_description' => $data['product_description'] ?? '',
            'redirect_post' => 'true',
            'checksum' => $checksum,
        ];

        if (isset($data['buyer_bank_code'])) {
            $payload['buyer_bank_code'] = $data['buyer_bank_code'];
        }

        $response = Http::asForm()->post($url, $payload);

        if (!$response->successful()) {
            Log::error('SecurePay Create Payment Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload
            ]);
            throw new \RuntimeException('SecurePay API failed: ' . $response->status());
        }

        // Sample code echoes output, which is likely HTML or a redirect URL string?
        // But usually API v1 returns JSON or text.
        // If the response is the payment page content (HTML), we might need to display it.
        // But 'redirect_post' = 'true' usually implies we get a URL or we need to POST to the URL.
        
        // Let's check response content type.
        // If it returns a URL text directly (some older gateways do this):
        $body = $response->body();
        
        // If body looks like a URL, return it wrapped.
        if (filter_var($body, FILTER_VALIDATE_URL)) {
             return ['payment_url' => $body];
        }

        // If JSON
        $json = $response->json();
        if ($json) {
            return $json;
        }

        // If HTML content (likely for redirect_post=true)
        if (str_contains($body, '<html') || str_contains($body, '<form')) {
            return ['html' => $body];
        }

        return ['response_body' => $body];
    }
}
