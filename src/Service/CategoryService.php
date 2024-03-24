<?php

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class CategoryService
{
    private $categoryRepository;
    private $entityManager;

    public function __construct(CategoryRepository $categoryRepository, EntityManagerInterface $entityManager)
    {
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
    }

    //findAll method
    /**
     * @return Category[]
     */
    public function findAll(): array
    {
        return $this->categoryRepository->findAll();
    }

    //find method
    public function find(int $id): Category
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw new \InvalidArgumentException('Category not found.');
        }

        return $category;
    }

    //create method
    public function create(string $title, string $image): Category
    {
        $category = new Category();
        $category->setTitle($title);
        $category->setImage($image);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    //update method
    public function update(int $id, array $requestData): Category
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw new \InvalidArgumentException('Category not found.');
        }

        if (isset($requestData['title'])) {
            $category->setTitle($requestData['title']);
        }

        if (isset($requestData['image'])) {
            $category->setImage($requestData['image']);
        }

        $this->entityManager->flush();

        return $category;
    }

    //delete method
    public function delete(int $id): void
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw new \InvalidArgumentException('Category not found.');
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }
}