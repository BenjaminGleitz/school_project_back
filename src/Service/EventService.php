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
use Symfony\Component\Security\Core\User\UserInterface;
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

    // Get all events
    public function findAll(): array
    {
        return $this->eventRepository->findAll();
    }

    // Get a single event
    public function find(int $id): Event
    {
        $event = $this->eventRepository->find($id);

        if (!$event) {
            throw new NotFoundHttpException('Event not found.');
        }

        return $event;
    }

    // Create a new event
    public function create(string $requestData): Event
    {
        $requestData = json_decode($requestData, true);

        $event = new Event();
        $event->setTitle($requestData['title']);
        $event->setDescription($requestData['description'] ?? null);
        $event->setStartAt(new \DateTimeImmutable($requestData['start_at']));

        $cityId = $this->cityService->find($requestData['city_id']);
        $categoryId = $this->categoryService->find($requestData['category_id']);
        $countryId = $this->countryService->find($requestData['country_id']);

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

    // Update an event
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
        if (!empty($requestData['participantLimit']) && $this->security->getUser() === $event->getCreator()) {
            $event->setParticipantLimit($requestData['participantLimit']);
        }
        if (!empty($requestData['country_id'])) {
            if ($event->getCity()->getCountry()->getId() != $requestData['country_id']) {
                throw new BadRequestHttpException('City does not belong to the country.');
            }
            $event->setCountry($this->entityManager->getReference('App\Entity\Country', $requestData['country_id']));
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

    // Delete an event
    public function delete(int $id): void
    {
        $event = $this->find($id);

        if (!$event) {
            throw new NotFoundHttpException('Event not found.');
        }

        $this->entityManager->remove($event);
        $this->entityManager->flush();
    }

    // Add a participant to an event
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

    // Remove a participant from an event
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

    // Get all events created by the currently logged in user
    public function findByCreator(UserInterface $user): array
    {
        return $this->eventRepository->findByCreatorQuery($user);
    }

    // Get all events that the currently logged in user is participating in
    public function findByParticipant(UserInterface $user): array
    {
        return $this->eventRepository->findByParticipantQuery($user);
    }
}
