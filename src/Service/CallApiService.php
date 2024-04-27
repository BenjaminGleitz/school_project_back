<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service permettant de récupérer des informations d'une série
 * à partir depuis le site https:rapidapi.com/wirefreethought/api/geodb-cities/
 */
class CallApiService

{
    private $client;

     public function __construct(HttpClientInterface $client)
         {
             $this->client = $client->withOptions([
                 'headers' => [
                     'x-rapidapi-host' => 'spott.p.rapidapi.com',
                     'x-rapidapi-key' => '47b095e0f8msh6780e41590cafb2p18df69jsn1ea5419d64b3'
                 ],
             ]);
         }

     public function getCountriesData(): array
     {
         $response = $this->client->request(
             'GET',
             'https://spott.p.rapidapi.com/places/autocomplete?type=COUNTRY&limit=5'
         );

         return $response->toArray();
     }

     public function getCitiesData($countryCode): array
     {
         $response = $this->client->request(
             'GET',
             'https://spott.p.rapidapi.com/places/autocomplete?country=' . $countryCode . '&type=CITY&limit=5'
         );

         return $response->toArray();
     }
}