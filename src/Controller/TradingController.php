<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/trading')]
class TradingController extends AbstractController
{
    #[Route('/', name: 'trading_dashboard', methods: ['GET'])]
    public function dashboard(): Response
    {
        // Trading dashboard
    }

    #[Route('/pairs', name: 'trading_pairs_list', methods: ['GET'])]
    public function listPairs(): Response
    {
        // List trading pairs
    }

    #[Route('/order/create', name: 'create_order', methods: ['POST'])]
    public function createOrder(): Response
    {
        // Create new order
    }

    #[Route('/order/{id}', name: 'get_order', methods: ['GET'])]
    public function getOrder(int $id): Response
    {
        // Get order details
    }

    #[Route('/order/{id}/cancel', name: 'cancel_order', methods: ['POST'])]
    public function cancelOrder(int $id): Response
    {
        // Cancel order
    }
}