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

    public function createStaff(array $data)
    {
        $url = $this->baseUrl . '/staffs';
        $body =[
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'addressId' => 1,
            'email'=> $data['email'],
            'username' => $data['username'],
            'storeId' => 1,
            'active' => true,
            'password' => $data['password'],
            'lastUpdate' => $data['lastUpdate']
        ];
        try {
            $request = Http :: acceptJson()
                ->timeout(5);

            $response = $request->post($url, $body);
            $status = $response->status();
            $responseBody = $response->json();

            if ($response->successful()) {
                return $responseBody;
            }
            return null;

        } catch (\Throwable $e) {
            return null;
        }
    }
}