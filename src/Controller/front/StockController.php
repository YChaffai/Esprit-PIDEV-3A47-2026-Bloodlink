<?php

namespace App\Controller\front;

use App\Repository\StockRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stock')]
class StockController extends AbstractController
{
     #[Route('/', name: 'front_stock_index', methods: ['GET'])]
    public function index(StockRepository $stockRepo): Response
    {
        // Pour le moment, on affiche tous les stocks
        $stocks = $stockRepo->findAll();

        return $this->render('front/stock.html.twig', [
            'stocks' => $stocks,
        ]);
    }
}
