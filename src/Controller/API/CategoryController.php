<?php

namespace App\Controller\API;

use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/category', name: 'api_category_')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CategoryService $categoryService, SerializerInterface $serializer): JsonResponse
    {
        try {
            $categories = $categoryService->findAll();
            $data = $serializer->serialize($categories, 'json', ['groups' => 'getCategory']);
            return new JsonResponse($data, 200, [], true);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(CategoryService $categoryService, SerializerInterface $serializer, int $id): JsonResponse
    {
        try {
            $category = $categoryService->find($id);
            $jsonContent = $serializer->serialize($category, 'json');
            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, CategoryService $categoryService): JsonResponse
    {
        try {
            $createdCategory = $categoryService->create($request->getContent());
            $jsonContent = $serializer->serialize($createdCategory, 'json', ['groups' => 'getCategory']);
            $violations = $validator->validate($createdCategory);
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

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(Request $request, SerializerInterface $serializer, CategoryService $categoryService, ValidatorInterface $validator, int $id): JsonResponse
    {
        try {
            $updatedCategory = $categoryService->update($id, $request->getContent());
            $jsonContent = $serializer->serialize($updatedCategory, 'json', ['groups' => 'getCategory']);

            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(CategoryService $categoryService, int $id): JsonResponse
    {
        try {
            $categoryService->delete($id);
            return $this->json(['message' => 'Category deleted successfully.'], 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
