<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryService
{
    private $categoryRepository;
    private $entityManager;

    public function __construct(CategoryRepository $categoryRepository, EntityManagerInterface $entityManager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->categoryRepository->findAll();
    }

    public function find(int $id): Category
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw new NotFoundHttpException('Category not found.');
        }

        return $category;
    }

    // category service
    public function create(string $requestData): Category
    {
        $requestData = json_decode($requestData, true);

        if (empty($requestData['title'])) {
            throw new BadRequestHttpException('Title is required.');
        }

        $category = new Category();
        $category->setTitle($requestData['title']);

        if (isset($requestData['image'])) {
            $category->setImage($requestData['image']);
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }


    public function update(int $id, string $requestData): Category
    {
        $requestData = json_decode($requestData, true);
        $category = $this->find($id);

        if (!empty($requestData['title'])) {
            $category->setTitle($requestData['title']);
        }

        if (isset($requestData['image'])) {
            $category->setImage($requestData['image']);
        }

        $this->entityManager->flush();

        return $category;
    }

    public function delete(int $id): void
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw new NotFoundHttpException('Category not found.');
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function getCategoryById(int $id): Category
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw new NotFoundHttpException('Category not found.');
        }

        return $category;
    }
}
