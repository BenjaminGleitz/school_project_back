<?php

namespace App\Controller\API;

use App\Service\CityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/city', name: 'api_city_')]
class CityController extends AbstractController
{
    //function to get all cities
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CityService $cityService, SerializerInterface $serializer): JsonResponse
    {
        try {
            $city = $cityService->findAll();
            $data = $serializer->serialize($city, 'json', ['groups' => 'getCity']);
            return new JsonResponse($data, 200, [], true);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    //function to get a city by id
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(SerializerInterface $serializer, CityService $cityService, int $id): JsonResponse
    {
        try {
            $city = $cityService->find($id);
            $jsonContent = $serializer->serialize($city, 'json', ['groups' => 'getCity']);
            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    //function to create a city
    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, CityService $cityService, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        try {
            $createdCity = $cityService->create($request->getContent());
            $jsonContent = $serializer->serialize($createdCity, 'json', ['groups' => 'getCity']);
            $violations = $validator->validate($createdCity);
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

    //function to update a city
    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(int $id, Request $request, CityService $cityService, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        try {
            $updatedCity = $cityService->update($id, $request->getContent());
            $jsonContent = $serializer->serialize($updatedCity, 'json', ['groups' => 'getCity']);
            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    //function to delete a city
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, CityService $cityService): JsonResponse
    {
        try {
            $cityService->delete($id);
            return $this->json(['message' => 'City deleted successfully'], 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

}
