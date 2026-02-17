<?php

namespace App\Controller\Back;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/commande')]
class BackCommandeController extends AbstractController
{
    #[Route('/', name: 'back_commandes_index', methods: ['GET'])]
    public function index(Request $request, CommandeRepository $commandeRepository): Response
    {
        $search = $request->query->get('search', '');
        $status = $request->query->get('status', '');
        $priority = $request->query->get('priority', '');

        $criteria = [
            'search' => $search,
            'status' => $status,
            'priority' => $priority
        ];

        $commandes = $commandeRepository->searchBy($criteria);

        if ($request->isXmlHttpRequest()) {
            return $this->render('back/commande/_commande_table.html.twig', [
                'commandes' => $commandes,
            ]);
        }

        return $this->render('back/Commande.html.twig', [
            'commandes' => $commandes,
            'search' => $search,
            'status' => $status,
            'priority' => $priority,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CommandeType::class, $commande, ['show_status' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Commande mise à jour.');
            return $this->redirectToRoute('back_commandes_index');
        }

        return $this->render('back/editCommande.html.twig', [
            'commande' => $commande,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->get('_token'))) {
            $em->remove($commande);
            $em->flush();
            $this->addFlash('success', 'Commande supprimée.');
        }

        return $this->redirectToRoute('back_commandes_index');
    }
}