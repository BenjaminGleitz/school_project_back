<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallApiService
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
        $this->apiKey = $_ENV['RAPIDAPI_KEY'];
    }

    public function getCountriesData(): array
    {
        $response = $this->client->request(
            'GET',
            'https://spott.p.rapidapi.com/places/autocomplete?type=COUNTRY&limit=35',
            [
                'headers' => [
                    'x-rapidapi-host' => 'spott.p.rapidapi.com',
                    'x-rapidapi-key' => $this->apiKey
                ]
            ]
        );

        return $response->toArray();
    }

    public function getCitiesData($countryCode): array
    {
        $response = $this->client->request(
            'GET',
            'https://spott.p.rapidapi.com/places/autocomplete?country=' . $countryCode . '&type=CITY&limit=15',
            [
                'headers' => [
                    'x-rapidapi-host' => 'spott.p.rapidapi.com',
                    'x-rapidapi-key' => $this->apiKey
                ]
            ]
        );

        return $response->toArray();
    }
}
