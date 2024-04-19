<?php

namespace App\Controller\Backoffice;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/city', name: 'app_city_')]
class CityController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CityRepository $cityRepository): Response
    {
        $cities = $cityRepository->findAll();

        return $this->render('city/index.html.twig', [
            'cities' => $cities,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $city = new City();
            $form = $this->createForm(CityType::class, $city);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($city);
                $entityManager->flush();

                return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('city/new.html.twig', [
                'city' => $city,
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            return $this->render('error/error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de la création de la ville.'
            ]);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(City $city): Response
    {
        try {
            return $this->render('city/show.html.twig', [
                'city' => $city,
            ]);
        } catch (NotFoundHttpException $e) {
            return $this->render('error/error.html.twig', [
                'message' => 'La ville demandée n\'existe pas.'
            ]);
        } catch (\Exception $e) {
            return $this->render('error/error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de l\'affichage de la ville.'
            ]);
        }
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, City $city, EntityManagerInterface $entityManager): Response
    {
        try {
            $form = $this->createForm(CityType::class, $city);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('city/edit.html.twig', [
                'city' => $city,
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            return $this->render('error/error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de la modification de la ville.'
            ]);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, City $city, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete'.$city->getId(), $request->getPayload()->get('_token'))) {
                $entityManager->remove($city);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_city_index', [], Response::HTTP_SEE_OTHER);
        } catch (\Exception $e) {
            return $this->render('error/error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de la suppression de la ville.'
            ]);
        }
    }
}