<?php

namespace App\Controller\back;

use App\Entity\Demande;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/demande')]
class DemandeController extends AbstractController
{
    #[Route('/', name: 'back_demande_index')]
    public function index(DemandeRepository $repo): Response
    {
        return $this->render('back/index.html.twig', [
            'demandes' => $repo->findAll(),
        ]);
    }

    #[Route('/{id}/valider', name: 'back_demande_valider')]
    public function valider(Demande $demande, EntityManagerInterface $em): Response
    {
        $demande->setStatus('VALIDEE');
        $em->flush();

        $this->addFlash('success', 'Demande validée');

        return $this->redirectToRoute('back_demande_index');
    }

    #[Route('/{id}/refuser', name: 'back_demande_refuser')]
    public function refuser(Demande $demande, EntityManagerInterface $em): Response
    {
        $demande->setStatus('REFUSEE');
        $em->flush();

        $this->addFlash('danger', 'Demande refusée');

        return $this->redirectToRoute('back_demande_index');
    }
}

