<?php

namespace App\Controller\API;

use App\Service\EventService;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/event', name: 'api_event_')]
class EventController extends AbstractController
{
    // Get all events
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EventService $eventService, SerializerInterface $serializer): JsonResponse
    {
        try {
            $events = $eventService->findAll();
            $data = $serializer->serialize($events, 'json', ['groups' => 'getEvent']);
            return new JsonResponse($data, 200, [], true);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Get a single event
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(SerializerInterface $serializer, EventService $eventService, int $id): JsonResponse
    {
        try {
            $event = $eventService->find($id);
            $jsonContent = $serializer->serialize($event, 'json', ['groups' => 'getOneEvent']);
            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Create a new event
    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, EventService $eventService, SerializerInterface $serializer): JsonResponse
    {
        try {
            $createdEvent = $eventService->create($request->getContent());
            $jsonContent = $serializer->serialize($createdEvent, 'json', ['groups' => 'getEvent']);
            return new JsonResponse($jsonContent, 201, [], true);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Update an event
    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(int $id, Request $request, EventService $eventService, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        try {
            $event = $eventService->find($id); // Get the event to be updated

            $currentUser = $this->getUser(); // Get the currently logged in user

            if ($currentUser->getId() !== $event->getCreator()->getId()) {
                // If the current user is not the creator of the event, return an error response
                return $this->json(['error' => 'You are not allowed to update this event'], 403);
            }

            $updatedEvent = $eventService->update($id, $request->getContent());
            $jsonContent = $serializer->serialize($updatedEvent, 'json', ['groups' => 'getOneEvent']);

            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Delete an event
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id, EventService $eventService): JsonResponse
    {
        try {
            $eventService->delete($id);
            return $this->json(['message' => 'Event deleted successfully'], 204);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Add a participant to an event
    #[Route('/{id}/participate', name: 'add_participant', methods: ['POST'])]
    public function addParticipant(int $id, Request $request, EventService $eventService, SerializerInterface $serializer): JsonResponse
    {
        try {
            $event = $eventService->addParticipant($id);
            $jsonContent = $serializer->serialize($event, 'json', ['groups' => 'getEvent']);
            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Remove a participant from an event
    #[Route('/{id}/remove-participation', name: 'remove_participant', methods: ['DELETE'])]
    public function removeParticipant(int $id, Request $request, EventService $eventService, SerializerInterface $serializer): JsonResponse
    {
        try {
            $event = $eventService->removeParticipant($id);
            $jsonContent = $serializer->serialize($event, 'json', ['groups' => 'getEvent']);
            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Get all events created by the currently logged in user
    #[Route('/my/events', name: 'my_events', methods: ['GET'])]
    public function getEventsCreatedByCurrentUser(EventService $eventService, SerializerInterface $serializer, Security $security): JsonResponse
    {
        try {
            $currentUser = $security->getUser();
            $events = $eventService->findByCreator($currentUser);
            $data = $serializer->serialize($events, 'json', ['groups' => 'getEvent']);
            return new JsonResponse($data, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // Get all events that the currently logged in user is participating in
    #[Route('/my/participations', name: 'my_participations', methods: ['GET'])]
    public function getEventsParticipatedByCurrentUser(EventService $eventService, SerializerInterface $serializer, Security $security): JsonResponse
    {
        try {
            $currentUser = $security->getUser();
            $events = $eventService->findByParticipant($currentUser);
            $data = $serializer->serialize($events, 'json', ['groups' => 'getEvent']);
            return new JsonResponse($data, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
