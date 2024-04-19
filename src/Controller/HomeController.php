<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app_')]
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {

        $welcome = "Bienvenue sur le backoffice :";

        return $this->render('home/index.html.twig', [
            'welcome_message' => $welcome
        ]);
    }
}
