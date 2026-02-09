<?php

namespace App\Controller\Front;

use App\Entity\Don;
use App\Form\DonType;
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

        // In your SQL: client.id = user.id
        $client = $clientRepo->find($user->getId());
        if (!$client) {
            throw $this->createNotFoundException('Client profile not found for this user.');
        }

        return $client;
    }

    // ✅ THIS is the route your navbar needs: front_don_index
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

    #[Route('/front/don/new', name: 'front_don_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($clientRepo);

        $don = new Don();
        $don->setDate(new \DateTime());   // default date
        $don->setIdEntite(1);             // DB requires NOT NULL (change if you have real entitycollecte)

        $form = $this->createForm(DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $don->setClient($client);

            $em->persist($don);
            $em->flush();

            $this->addFlash('success', 'Donation added.');
            return $this->redirectToRoute('front_don_index');
        }

        return $this->render('front/don/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front/don/{id}/edit', name: 'front_don_edit', methods: ['GET', 'POST'])]
    public function edit(Don $don, Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($clientRepo);

        // ✅ Ownership check
        if ($don->getClient()?->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException('Not your donation.');
        }

        $form = $this->createForm(DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Donation updated.');
            return $this->redirectToRoute('front_don_index');
        }

        return $this->render('front/don/edit.html.twig', [
            'form' => $form->createView(),
            'don' => $don,
        ]);
    }

    #[Route('/front/don/{id}/delete', name: 'front_don_delete', methods: ['POST'])]
    public function delete(Don $don, Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($clientRepo);

        if ($don->getClient()?->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException('Not your donation.');
        }

        if ($this->isCsrfTokenValid('delete_don_'.$don->getId(), (string)$request->request->get('_token'))) {
            $em->remove($don);
            $em->flush();
            $this->addFlash('success', 'Donation deleted.');
        }

        return $this->redirectToRoute('front_don_index');
    }
}
