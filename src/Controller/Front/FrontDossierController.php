<?php

namespace App\Controller\Front;

use App\Entity\DossierMed;
use App\Form\DossierMedType;
use App\Repository\ClientRepository;
use App\Repository\DossierMedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontDossierController extends AbstractController
{
    private function currentClient(ClientRepository $clientRepo)
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        return $clientRepo->find($user->getId());
    }

    #[Route('/front/dossier', name: 'front_dossier_show', methods: ['GET'])]
   #[Route('/front/dossier', name: 'front_dossier_show', methods: ['GET'])]
    public function index(DossierMedRepository $repo, ClientRepository $clientRepo, Request $request): Response
    {
        $client = $this->currentClient($clientRepo);

        // 1. Get parameters from URL (e.g. ?q=plasma&sort=date&dir=ASC)
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'date'); // Default sort by date
        $direction = $request->query->get('dir', 'DESC'); // Default direction DESC

        // 2. Use custom repository method
        $dossiers = $repo->findByClientSearchAndSort($client, $search, $sort, $direction);

        return $this->render('front/dossier/index.html.twig', [
            'dossiers' => $dossiers,
            // Pass params back to view to keep form filled
            'currentSearch' => $search,
            'currentSort' => $sort,
            'currentDir' => $direction
        ]);
    }

    #[Route('/front/dossier/{id}/edit', name: 'front_dossier_edit', methods: ['GET', 'POST'])]
    public function edit(DossierMed $dossier, Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($clientRepo);

        // Security: Ownership check
        if ($dossier->getClient()?->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException('Ce dossier ne vous appartient pas.');
        }

        $form = $this->createForm(DossierMedType::class, $dossier, [
            'client' => $client,
        ]);

        // 🔒 LOCK FIELDS: Client cannot change these
        $form->remove('nom');
        $form->remove('prenom');
        $form->remove('typeSang');
        $form->remove('don');
        $form->remove('sexe'); // Assuming gender is also non-modifiable identity data

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Dossier mis à jour avec succès.');
            return $this->redirectToRoute('front_dossier_show');
        }

        return $this->render('front/dossier/edit.html.twig', [
            'form' => $form->createView(),
            'dossier' => $dossier, // Pass this to display the locked values as text
        ]);
    }

    #[Route('/front/dossier/{id}/delete', name: 'front_dossier_delete', methods: ['POST'])]
    public function delete(DossierMed $dossier, Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($clientRepo);

        if ($dossier->getClient()?->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete_dossier_'.$dossier->getId(), (string)$request->request->get('_token'))) {
            $em->remove($dossier);
            $em->flush();
            $this->addFlash('success', 'Dossier médical supprimé.');
        }

        return $this->redirectToRoute('front_dossier_show');
    }
}