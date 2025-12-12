<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ToyyibpayService
{
    public function baseUrl(): string
    {
        $base = rtrim(env('TOYYIBPAY_BASE_URL', ''), '/');
        if (empty($base)) {
            throw new \RuntimeException('TOYYIBPAY_BASE_URL tidak ditetapkan.');
        }
        return $base;
    }

    public function secret(): string
    {
        $secret = env('TOYYIBPAY_SECRET', '');
        if (empty($secret)) {
            throw new \RuntimeException('TOYYIBPAY_SECRET tidak ditetapkan.');
        }
        return $secret;
    }

    public function categoryCode(): string
    {
        $cat = env('TOYYIBPAY_CATEGORY_CODE', '');
        if (empty($cat)) {
            throw new \RuntimeException('TOYYIBPAY_CATEGORY_CODE tidak ditetapkan.');
        }
        return $cat;
    }

    public function createBill(array $payload): array
    {
        $url = $this->baseUrl().'/index.php/api/createBill';
        $response = Http::asForm()->post($url, $payload);
        if (!$response->successful()) {
            throw new \RuntimeException('Toyyibpay API gagal: '.$response->status());
        }
        $raw = (string) $response->body();
        $data = json_decode($raw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Respon Toyyibpay tidak sah.');
        }
        if (isset($data['status']) && $data['status'] === 'error') {
            $msg = $data['msg'] ?? 'Ralat tidak diketahui.';
            throw new \RuntimeException('Toyyibpay ralat: '.$msg);
        }
        if (isset($data[0]['BillCode'])) {
            return $data[0];
        }
        if (isset($data['BillCode'])) {
            return $data;
        }
        throw new \RuntimeException('Respon Toyyibpay tidak sah.');
    }
}
