<?php

namespace App\Controller;

use App\Service\CountryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/country', name: 'country_')]
class CountryController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CountryService $countryService): JsonResponse
    {
        $countries = $countryService->getAllCountries();
        return $this->json($countries);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(CountryService $countryService, int $id): JsonResponse
    {
        try {
            $country = $countryService->getCountry($id);
            return $this->json($country);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, CountryService $countryService): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $name = $requestData['name'] ?? '';

        try {
            $newCountry = $countryService->createCountry($name);
            return $this->json($newCountry, 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, CountryService $countryService, int $id): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
        $name = $requestData['name'] ?? '';

        try {
            $countryService->updateCountry($id, $name);
            $updatedCountry = $countryService->getCountry($id);
            return $this->json($updatedCountry);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(CountryService $countryService, int $id): JsonResponse
    {
        try {
            $countryService->deleteCountry($id);
            return new JsonResponse(null, 204);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}
