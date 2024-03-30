<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class EventService
{
    private $eventRepository;
    private $entityManager;

    public function __construct(EventRepository $eventRepository, EntityManagerInterface $entityManager)
    {
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->eventRepository->findAll();
    }

    public function find(int $id): Event
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            throw new \InvalidArgumentException('Event not found.');
        }

        return $event;
    }

    public function create(string $title, string $description, int $cityId, int $categoryId, \DateTimeImmutable $startAt): Event
    {
        $city = $this->getCityById($cityId);
        $category = $this->getCategoryById($categoryId);

        if (!$city) {
            throw new \InvalidArgumentException('The specified city does not exist.');
        }

        if (!$category) {
            throw new \InvalidArgumentException('The specified category does not exist.');
        }

        $event = new Event();
        $event->setTitle($title);
        $event->setDescription($description);
        $event->setCity($city);
        $event->setCategory($category); // Modification ici
        $event->setStartAt($startAt);

        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return $event;
    }


    public function getCityById(int $cityId): ?City
    {
        return $this->entityManager->getRepository(City::class)->find($cityId);
    }

    public function getCategoryById(int $categoryId): ?Category
    {
        return $this->entityManager->getRepository(Category::class)->find($categoryId);
    }
}