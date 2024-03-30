<?php

namespace App\Controller;

use App\Entity\Event;
use App\Service\EventService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\CircularReferenceException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/event', name: 'event_')]
class EventController extends AbstractController
{
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

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(SerializerInterface $serializer, EventService $eventService, int $id): JsonResponse
    {
        try {
            $event = $eventService->find($id);
            $jsonContent = $serializer->serialize($event, 'json', ['groups' => 'getEvent']);
            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, EventService $eventService, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        try {
            $createdEvent = $eventService->create($request->getContent());
            $jsonContent = $serializer->serialize($createdEvent, 'json', ['groups' => 'getEvent']);
            $violations = $validator->validate($createdEvent);
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
    public function update(int $id, Request $request, EventService $eventService, SerializerInterface $serializer, ValidatorInterface $validator): JsonResponse
    {
        try {
            $updatedEvent = $eventService->update($id, $request->getContent());
            $jsonContent = $serializer->serialize($updatedEvent, 'json', ['groups' => 'getEvent']);

            return new JsonResponse($jsonContent, 200, [], true);
        } catch (NotFoundHttpException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

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
}
