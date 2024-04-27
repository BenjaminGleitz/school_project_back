<?php

namespace App\Service;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\Country;
use App\Entity\Event;
use App\Entity\User;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EventService
{
    private $eventRepository;
    private $entityManager;
    private $cityService;
    private $categoryService;
    private $countryService;
    private $validator;
    private $security;

    public function __construct(
        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager,
        CityService            $cityService,
        CategoryService        $categoryService,
        ValidatorInterface     $validator,
        CountryService         $countryService,
        Security               $security
    )
    {
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
        $this->cityService = $cityService;
        $this->categoryService = $categoryService;
        $this->validator = $validator;
        $this->countryService = $countryService;
        $this->security = $security;
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
        $categoryId = $this->getCategoryById($requestData['category_id']);
        $countryId = $this->getCountryById($requestData['country_id']);

        $event->setCity($cityId);
        $event->setCategory($categoryId);
        $event->setCountry($countryId);
        $event->setCreator($this->security->getUser());

        if (isset($requestData['participantLimit'])) {
            $event->setParticipantLimit($requestData['participantLimit']);
        }

        if ($cityId->getCountry()->getId() != $countryId->getId()) {
            throw new BadRequestHttpException('City does not belong to the country.');
        }

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
        if (!empty($requestData['participantLimit'])) {
            $event->setParticipantLimit($requestData['participantLimit']);
            dd($requestData['participantLimit']);
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

    public function addParticipant(int $eventId): Event
    {
        $user = $this->security->getUser();
        assert($user instanceof User, 'User is not authenticated.');

        $event = $this->find($eventId);

        if (!$event) {
            throw new NotFoundHttpException('Event not found.');
        }

        if ($event->getCreator()->getId() === $user->getId()) {
            throw new BadRequestHttpException('Creator cannot participate in their own event.');
        }

        if ($event->getParticipant()->contains($user)) {
            throw new BadRequestHttpException('User is already a participant.');
        }

        if ($event->getParticipantLimit() == !null) {
            if ($event->getParticipant()->count() >= $event->getParticipantLimit()) {
                throw new BadRequestHttpException('Event is full.');
            }
        }

        $event->addParticipant($user);
        $this->entityManager->flush();

        return $event;
    }

    public function removeParticipant(int $eventId): Event
    {
        $user = $this->security->getUser();
        assert($user instanceof User, 'User is not authenticated.');

        $event = $this->find($eventId);

        if (!$event) {
            throw new NotFoundHttpException('Event not found.');
        }

        if (!$event->getParticipant()->contains($user)) {
            throw new BadRequestHttpException('User is not a participant.');
        }

        $event->removeParticipant($user);
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

    public function getCountryById(int $id): Country
    {
        if (!$this->countryService->find($id)) {
            throw new NotFoundHttpException('Country not found.');
        }

        return $this->countryService->find($id);
    }
}
