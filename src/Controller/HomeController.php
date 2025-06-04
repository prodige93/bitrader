<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // For testing, return a simple response
        return new Response('<h1>Bienvenue sur notre plateforme</h1><p>Page d\'accueil simplifiée pour test.</p>');
    }
}
