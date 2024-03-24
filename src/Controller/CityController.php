<?php

namespace App\Controller;

use App\Service\CityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/city', name: 'city_')]
class CityController extends AbstractController
{
    //function to get all cities
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CityService $cityService): JsonResponse
    {
        $cities = $cityService->findAll();
        $formattedCities = [];

        foreach ($cities as $city) {
            $formattedCities[] = [
                'id' => $city->getId(),
                'name' => $city->getName(),
                'country' => $city->getCountry() ? $city->getCountry()->getName() : null,
            ];
        }

        return $this->json($formattedCities, 200);
    }

    //function to get a city by id
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(CityService $cityService, int $id): JsonResponse
    {
        try {
            $city = $cityService->find($id);
            $formattedCity = [
                'id' => $city->getId(),
                'name' => $city->getName(),
                'country' => $city->getCountry() ? $city->getCountry()->getName() : null,
            ];

            return $this->json($formattedCity, 200);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    //function to create a city
    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, CityService $cityService): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        // Check if the required fields are provided in the request
        if (!isset($requestData['name'], $requestData['country_id'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        try {
            // Try to create the city with the provided data
            $city = $cityService->create($requestData['name'], $requestData['country_id']);

            // Format the response data
            $formattedCity = [
                'id' => $city->getId(),
                'name' => $city->getName(),
                'country' => $city->getCountry() ? $city->getCountry()->getName() : null,
            ];

            // Return the formatted city data in the response
            return $this->json($formattedCity, 201);
        } catch (\InvalidArgumentException $e) {
            // Catch the exception thrown by the CityService and return an error response
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    //function to update a city
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, CityService $cityService, int $id): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData['name'], $requestData['country_id'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        try {
            $city = $cityService->update($id, $requestData['name'], $requestData['country_id']);
            $formattedCity = [
                'id' => $city->getId(),
                'name' => $city->getName(),
                'country' => $city->getCountry() ? $city->getCountry()->getName() : null,
            ];

            return $this->json($formattedCity, 200);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404); // Retourner 404 si la ville n'est pas trouvée
        }
    }

    //function to delete a city
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(CityService $cityService, int $id): JsonResponse
    {
        try {
            $cityService->delete($id);
            return new JsonResponse(null, 204);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }
}
