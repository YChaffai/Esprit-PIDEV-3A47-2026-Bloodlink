<?php

namespace App\Controller\Front;

use App\Entity\Don;
use App\Repository\DonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontDonController extends AbstractController
{
    private function currentClient()
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Return client if user is a client, otherwise return null (Admins/Doctors)
        return $user->getClient();
    }

    #[Route('/front/don/new', name: 'front_don_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $client = $this->currentClient();

        $don = new Don();
        $don->setClient($client);
        $don->setDate(new \DateTime());

        $form = $this->createForm(\App\Form\DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($don);
            $em->flush();

            $this->addFlash('success', 'Votre don a été enregistré avec succès.');
            return $this->redirectToRoute('front_don_index');
        }

        return $this->render('front/don/new.html.twig', [
            'don' => $don,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front/don', name: 'front_don_index', methods: ['GET'])]
    public function index(DonRepository $donRepo, Request $request): Response
    {
        // 1. Get Current Client (Can be null for Admins)
        $client = $this->currentClient();

        // 2. Get parameters from URL
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'date'); // Default: Date
        $direction = $request->query->get('dir', 'DESC'); // Default: Newest first

        // 3. Fetch Data
        $dons = $donRepo->findByClientSearchAndSort($client, $search, $sort, $direction);

        return $this->render('front/don/index.html.twig', [
            'dons' => $dons,
            // Pass params back to view to keep UI state
            'currentSearch' => $search,
            'currentSort' => $sort,
            'currentDir' => $direction
        ]);
    }

    #[Route('/front/don/{id}/delete', name: 'front_don_delete', methods: ['POST'])]
    public function delete(Don $don, Request $request, EntityManagerInterface $em): Response
    {
        $client = $this->currentClient();

        // SECURITY: Check ownership (Bypassed for Admins)
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        if (!$isAdmin && $don->getClient()?->getId() !== $client?->getId()) {
            throw $this->createAccessDeniedException('Ce don ne vous appartient pas.');
        }

        if ($this->isCsrfTokenValid('delete_don_' . $don->getId(), (string) $request->request->get('_token'))) {
            $em->remove($don);
            $em->flush();
            $this->addFlash('success', 'Don supprimé.');
        }

        return $this->redirectToRoute('front_don_index');
    }
}
