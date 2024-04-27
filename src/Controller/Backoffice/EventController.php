<?php

namespace App\Controller\Backoffice;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/backoffice/event', name: 'app_event_')]
class EventController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $event = new Event();
            $form = $this->createForm(EventType::class, $event);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($event);
                $entityManager->flush();

                return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('event/new.html.twig', [
                'event' => $event,
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            return $this->render('error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de la création d\'un nouvel événement.'
            ]);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        try {
            return $this->render('event/show.html.twig', [
                'event' => $event,
            ]);
        } catch (NotFoundHttpException $e) {
            return $this->render('error.html.twig', [
                'message' => 'L\'événement demandé n\'existe pas.'
            ]);
        } catch (\Exception $e) {
            return $this->render('error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de l\'affichage de l\'événement.'
            ]);
        }
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        try {
            $form = $this->createForm(EventType::class, $event);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('event/edit.html.twig', [
                'event' => $event,
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            return $this->render('error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de la modification de l\'événement.'
            ]);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->getPayload()->get('_token'))) {
                $entityManager->remove($event);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        } catch (\Exception $e) {
            return $this->render('error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de la suppression de l\'événement.'
            ]);
        }
    }
}
