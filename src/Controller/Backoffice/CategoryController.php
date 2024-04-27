<?php

namespace App\Controller\Backoffice;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/backoffice/category', name: 'app_category_')]
class CategoryController extends AbstractController
{
    private $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->categoryService->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        try {
            $category = new Category();
            $form = $this->createForm(CategoryType::class, $category);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($category);
                $entityManager->flush();

                return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('category/new.html.twig', [
                'category' => $category,
                'form' => $form->createView(),
            ]);
        } catch (\Exception $e) {
            return $this->render('error/error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de la création de la catégorie.'
            ]);
        }
    }


    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        try {
            return $this->render('category/show.html.twig', [
                'category' => $category,
            ]);
        } catch (NotFoundHttpException $e) {
            return $this->render('error/error.html.twig', [
                'message' => 'La catégorie demandée n\'existe pas.'
            ]);
        } catch (\Exception $e) {
            return $this->render('error/error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de l\'affichage de la catégorie.'
            ]);
        }
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        try {
            if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->getPayload()->get('_token'))) {
                $entityManager->remove($category);
                $entityManager->flush();
            }

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_SEE_OTHER);
        } catch (\Exception $e) {
            return $this->render('error.html.twig', [
                'message' => 'Une erreur s\'est produite lors de la suppression de la catégorie.'
            ]);
        }
    }
}
