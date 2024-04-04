<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventService
{
    private $eventRepository;
    private $entityManager;
    private $cityService;
    private $categoryService;
    private $validator;

    public function __construct(EventRepository $eventRepository, EntityManagerInterface $entityManager, CityService $cityService, CategoryService $categoryService, ValidatorInterface $validator)
    {
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
        $this->cityService = $cityService;
        $this->categoryService = $categoryService;
        $this->validator = $validator;

    }

    public function findAll(): array
    {
        return $this->eventRepository->findAll();
    }

    public function find(int $id): Event
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            throw new NotFoundHttpException('Event not found.');
        }

        return $event;
    }

    public function create(string $requestData): Event
    {
        $requestData = json_decode($requestData, true);

        $event = new Event();
        $event->setTitle($requestData['title']);
        $event->setDescription($requestData['description'] ?? null);
        $event->setStartAt(new \DateTimeImmutable($requestData['start_at']));

        $cityId = $this->getCityById($requestData['city_id']);
        $event->setCity($cityId);

        $categoryId = $this->getCategoryById($requestData['category_id']);
        $event->setCategory($categoryId);

        $violations = $this->validator->validate($event);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new BadRequestHttpException(json_encode($errors));
        }

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $event;
    }

    public function getCityById(int $id): City
    {
        if (!$this->cityService->find($id)) {
            throw new NotFoundHttpException('City not found.');
        }

        return $this->cityService->find($id);
    }

    public function getCategoryById(int $id): Category
    {
        if (!$this->categoryService->find($id)) {
            throw new NotFoundHttpException('Category not found.');
        }

        return $this->categoryService->find($id);
    }

    public function update(int $id, string $requestData): Event
    {
        $requestData = json_decode($requestData, true);
        $event = $this->find($id);

        if (!empty($requestData['title'])) {
            $event->setTitle($requestData['title']);
        }
        if (!empty($requestData['description'])) {
            $event->setDescription($requestData['description']);
        }
        if (!empty($requestData['start_at'])) {
            $event->setStartAt(new \DateTimeImmutable($requestData['start_at']));
        }
        if (!empty($requestData['city_id'])) {
            $event->setCity($this->entityManager->getReference('App\Entity\City', $requestData['city_id']));
        }
        if (!empty($requestData['category_id'])) {
            $event->setCategory($this->entityManager->getReference('App\Entity\Category', $requestData['category_id']));
        }

        $violations = $this->validator->validate($event);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new BadRequestHttpException(json_encode($errors));
        }

        $this->entityManager->flush();

        return $event;
    }

    public function delete(int $id): void
    {
        $event = $this->find($id);

        if (!$event) {
            throw new NotFoundHttpException('Event not found.');
        }

        $this->entityManager->remove($event);
        $this->entityManager->flush();
    }
}
