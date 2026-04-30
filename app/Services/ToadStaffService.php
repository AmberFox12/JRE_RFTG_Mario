<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToadStaffService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.toad.url', 'http://localhost:8180'), '/');
    }

    public function createStaff(array $data): ?array
    {
        $url = $this->baseUrl . '/staffs';
        $body = [
            'firstName'  => $data['firstName'],
            'lastName'   => $data['lastName'],
            'addressId'  => 1,
            'email'      => $data['email'],
            'username'   => $data['username'],
            'storeId'    => 1,
            'active'     => true,
            'password'   => $data['password'],
            'lastUpdate' => $data['lastUpdate'],
        ];

        try {
            $response = Http::acceptJson()
                ->timeout(5)
                ->post($url, $body);

            $status       = $response->status();
            $responseBody = $response->json();

            Log::info('CreateStaff API', ['status' => $status]);

            if ($response->successful()) {
                return $responseBody;
            }

            Log::warning('CreateStaff API KO', ['status' => $status, 'body' => $responseBody]);
            return null;

        } catch (\Throwable $e) {
            Log::error('Erreur createStaff', ['msg' => $e->getMessage()]);
            return null;
        }
    }
}
