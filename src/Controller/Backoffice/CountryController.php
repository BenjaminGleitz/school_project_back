<?php

namespace App\Controller\Backoffice;

use App\Entity\Country;
use App\Form\CountryType;
use App\Repository\CountryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/country', name: 'app_country_')]
class CountryController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(CountryRepository $countryRepository): Response
    {
        $countries = $countryRepository->findAll();

        return $this->render('country/index.html.twig', [
            'countries' => $countries,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $country = new Country();
            $form = $this->createForm(CountryType::class, $country);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($country);
                $entityManager->flush();

                return $this->redirectToRoute('app_country_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('country/new.html.twig', [
                'country' => $country,
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            return $this->render('error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de la création d\'un nouveau pays.'
            ]);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Country $country): Response
    {
        try {
            return $this->render('country/show.html.twig', [
                'country' => $country,
            ]);
        } catch (NotFoundHttpException $e) {
            return $this->render('error.html.twig', [
                'message' => 'Le pays demandé n\'existe pas.'
            ]);
        } catch (\Exception $e) {
            return $this->render('error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de l\'affichage du pays.'
            ]);
        }
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Country $country, EntityManagerInterface $entityManager): Response
    {
        try {
            $form = $this->createForm(CountryType::class, $country);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                return $this->redirectToRoute('app_country_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('country/edit.html.twig', [
                'country' => $country,
                'form' => $form,
            ]);
        } catch (\Exception $e) {
            return $this->render('error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de la modification du pays.'
            ]);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Country $country, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete' . $country->getId(), $request->getPayload()->get('_token'))) {
                $entityManager->remove($country);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_country_index', [], Response::HTTP_SEE_OTHER);
        } catch (\Exception $e) {
            return $this->render('error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de la suppression du pays.'
            ]);
        }
    }
}
