<?php

namespace App\Controller\Front;

use App\Entity\Don;
use App\Repository\ClientRepository;
use App\Repository\DonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontDonController extends AbstractController
{
    private function currentClient(ClientRepository $clientRepo)
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in.');
        }

        $client = $clientRepo->find($user->getId());
        if (!$client) {
            throw $this->createNotFoundException('Client profile not found for this user.');
        }

        return $client;
    }

    #[Route('/front/don', name: 'front_don_index', methods: ['GET'])]
    public function index(DonRepository $donRepo, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($clientRepo);

        $dons = $donRepo->findBy(
            ['client' => $client],
            ['date' => 'DESC']
        );

        return $this->render('front/don/index.html.twig', [
            'dons' => $dons,
        ]);
    }

    // ❌ NOTE: 'new' and 'edit' methods are intentionally removed to prevent client creation/modification of donation data.

    #[Route('/front/don/{id}/delete', name: 'front_don_delete', methods: ['POST'])]
    public function delete(Don $don, Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($clientRepo);

        // SECURITY: Check ownership
        if ($don->getClient()?->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException('Not your donation.');
        }

        if ($this->isCsrfTokenValid('delete_don_'.$don->getId(), (string) $request->request->get('_token'))) {
            $em->remove($don);
            $em->flush();
            $this->addFlash('success', 'Don supprimé.');
        }

        return $this->redirectToRoute('front_don_index');
    }
}