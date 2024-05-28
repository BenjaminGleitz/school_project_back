<?php

namespace App\Controller\API;

use App\Service\CountryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/country', name: 'api_country_')]
class CountryController extends AbstractController
{
    //function to get all countries
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CountryService $countryService, SerializerInterface $serializer): JsonResponse
    {
        try {
            $countries = $countryService->findAll();
            $data = $serializer->serialize($countries, 'json', ['groups' => 'getCountry']);
            return new JsonResponse($data, 200, [], true);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    //function to get a country by id
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(SerializerInterface $serializer, CountryService $countryService, int $id): JsonResponse
    {
        try {
            $country = $countryService->find($id);
            $jsonContent = $serializer->serialize($country, 'json', ['groups' => 'getCountry']);
            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    //function to create a country
    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, CountryService $countryService, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        try {
            $createdCountry = $countryService->create($request->getContent());
            $jsonContent = $serializer->serialize($createdCountry, 'json', ['groups' => 'getCountry']);
            $violations = $validator->validate($createdCountry);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                return $this->json($errors, 400);
            }
            return new JsonResponse($jsonContent, 201, [], true);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    //function to update a country
    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(int $id, Request $request, CountryService $countryService, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        try {
            $updatedCountry = $countryService->update($id, $request->getContent());
            $jsonContent = $serializer->serialize($updatedCountry, 'json', ['groups' => 'getCountry']);

            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    //function to delete a country
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, CountryService $countryService): JsonResponse
    {
        try {
            $countryService->delete($id);
            return $this->json(['message' => 'Country deleted successfully'], 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
