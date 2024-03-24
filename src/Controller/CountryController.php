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
    //action to get all countries
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CountryService $countryService): JsonResponse
    {
        $countries = $countryService->findAll();

        return $this->json($countries);
    }

    //action to get a country by id
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(CountryService $countryService, int $id): JsonResponse
    {
        try {
            $country = $countryService->find($id);
            return $this->json($country);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    //action to create a country
    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, CountryService $countryService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? '';

        try {
            $category = $countryService->create($name);
            return $this->json($category, 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    //action to update a country
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, CountryService $countryService, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $name = $data['name'] ?? '';

        try {
            $country = $countryService->update($id, $name);
            return $this->json($country);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    //action to delete a country
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(CountryService $countryService, int $id): JsonResponse
    {
        try {
            $countryService->delete($id);
            return new JsonResponse(null, 204);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }
}
