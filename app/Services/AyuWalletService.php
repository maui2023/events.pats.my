<?php

namespace App\Services;

use Exception;

class AyuWalletService
{
    private string $host;
    private string $port;
    private string $user;
    private string $pass;

    public function __construct()
    {
        $this->host = env('AYU_RPC_HOST', '192.168.1.211');
        $this->port = env('AYU_RPC_PORT', '32720');
        $this->user = env('AYU_RPC_USER', 'yourname');
        $this->pass = env('AYU_RPC_PASS', 'MustLongPasswordWithNumber');
    }

    private function call(string $method, array $params = [])
    {
        $url = "http://{$this->host}:{$this->port}/";
        $payload = json_encode([
            'jsonrpc' => '1.0',
            'id' => 'ayucoin',
            'method' => $method,
            'params' => $params,
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->user}:{$this->pass}");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        if ($response === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new Exception('RPC Error: ' . $err);
        }
        curl_close($ch);

        $result = json_decode($response, true);
        if (isset($result['error']) && $result['error'] !== null) {
            throw new Exception('RPC Error: ' . ($result['error']['message'] ?? 'unknown'));
        }

        return $result['result'] ?? null;
    }

    public function getBalance(string $account = ''): float
    {
        $res = $this->call('getbalance', [$account]);
        return (float) $res;
    }

    public function getNewAddress(string $account = ''): string
    {
        $address = (string) $this->call('getnewaddress', [$account]);
        $this->call('setaccount', [$address, $account]);
        return $address;
    }
}

