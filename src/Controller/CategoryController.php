<?php

namespace App\Controller;

use App\Service\CategoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/category', name: 'category_')]
class CategoryController extends AbstractController
{
    //action to get all categories
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CategoryService $categoryService): JsonResponse
    {
        $categories = $categoryService->findAll();

        return $this->json($categories);
    }

    //action to get a category by id
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(CategoryService $categoryService, int $id): JsonResponse
    {
        try {
            $category = $categoryService->find($id);
            return $this->json($category);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    //action to create a category
    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, CategoryService $categoryService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? '';
        $image = $data['image'] ?? '';

        try {
            $category = $categoryService->create($title, $image);
            return $this->json($category, 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    //action to update a category
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, CategoryService $categoryService, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $title = $data['title'] ?? '';
        $image = $data['image'] ?? '';

        try {
            $category = $categoryService->update($id, $title, $image);
            return $this->json($category);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    //action to delete a category
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(CategoryService $categoryService, int $id): JsonResponse
    {
        try {
            $categoryService->delete($id);
            return new JsonResponse(null, 204);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}