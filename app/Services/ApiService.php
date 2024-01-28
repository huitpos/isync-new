<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ApiService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function makeRequest($method, $url, $data = [])
    {
        $token = session('_apiToken');

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        try {
            $response = $this->client->request($method, $url, [
                'headers' => $headers,
                'json' => $data,
            ]);

            return json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            // Handle exceptions (e.g., connection issues, timeouts)
            // You can log errors or handle them based on your application's needs
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}
