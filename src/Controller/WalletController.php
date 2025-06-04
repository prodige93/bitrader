<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/wallet')]
class WalletController extends AbstractController
{
    #[Route('/', name: 'wallet_overview', methods: ['GET'])]
    public function overview(): Response
    {
        // Wallet overview
    }

    #[Route('/deposit', name: 'wallet_deposit', methods: ['GET', 'POST'])]
    public function deposit(): Response
    {
        // Handle deposit
    }

    #[Route('/withdraw', name: 'wallet_withdraw', methods: ['GET', 'POST'])]
    public function withdraw(): Response
    {
        // Handle withdrawal
    }

    #[Route('/transactions', name: 'wallet_transactions', methods: ['GET'])]
    public function transactions(): Response
    {
        // List transactions
    }
}