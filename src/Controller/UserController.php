<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/profile', name: 'user_profile', methods: ['GET'])]
    public function profile(): Response
    {
        // User profile
    }

    #[Route('/profile/edit', name: 'user_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(): Response
    {
        // Edit profile
    }

    #[Route('/security', name: 'user_security', methods: ['GET', 'POST'])]
    public function security(): Response
    {
        // Security settings
    }
}