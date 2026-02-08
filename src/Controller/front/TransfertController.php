<?php

namespace App\Controller\front;

use App\Entity\Demande;
use App\Repository\TransfertRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transfert')]
class TransfertController extends AbstractController
{
    #[Route('/', name: 'front_transfert_index', methods: ['GET'])]
    public function index(TransfertRepository $transfertRepository): Response
    {
        // Récupérer tous les transferts
        $transferts = $transfertRepository->findAll();

        return $this->render('front/index.html.twig', [
            'transferts' => $transferts,
        ]);
    }

    #[Route('/{id}', name: 'front_transfert_show', methods: ['GET'])]
    public function show($id, TransfertRepository $transfertRepository): Response
    {
        $transfert = $transfertRepository->find($id);

        return $this->render('front/show.html.twig', [
            'transfert' => $transfert,
        ]);
    }

    #[Route('/demande/{id}', name: 'front_transfert_by_demande')]
    public function byDemande(Demande $demande, TransfertRepository $repo): Response
    {
        $transferts = $repo->findBy(['demande' => $demande]);

        return $this->render('front/index.html.twig', [
            'transferts' => $transferts,
        ]);
    }
}
