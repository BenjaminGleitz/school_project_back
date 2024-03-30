<?php

namespace App\Controller;

use App\Service\EventService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/event', name: 'event_', methods: ['GET'])]
class EventController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(EventService $eventService): JsonResponse
    {
        $events = $eventService->findAll();

        return $this->json($events);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(EventService $eventService, int $id): JsonResponse
    {
        try {
            $event = $eventService->find($id);
            return $this->json($event);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('/', name: 'create', methods: ['POST'])]
    public function create(Request $request, EventService $eventService): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);

        if (!isset($requestData['title'], $requestData['description'], $requestData['city'], $requestData['start_at'], $requestData['category'])) {
            return $this->json(['error' => 'Missing required fields'], 400);
        }

        try {
            $event = $eventService->create(
                $requestData['title'],
                $requestData['description'],
                $requestData['city'],
                $requestData['category'],
                new \DateTimeImmutable($requestData['start_at'])
            );
            return $this->json($event, 201);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

}
