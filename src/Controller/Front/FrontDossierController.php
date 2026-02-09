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

        // In your SQL: client.id = user.id
        $client = $clientRepo->find($user->getId());
        if (!$client) {
            throw $this->createNotFoundException('Client profile not found.');
        }
        return $client;
    }

    #[Route('/front/dossier', name: 'front_dossier_show', methods: ['GET'])]
    public function index(DossierMedRepository $repo, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($clientRepo);

        $dossiers = $repo->findBy(['client' => $client], ['id' => 'DESC']);

        return $this->render('front/dossier/index.html.twig', [
            'dossiers' => $dossiers,
        ]);
    }

    #[Route('/front/dossier/new', name: 'front_dossier_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($clientRepo);

        $dossier = new DossierMed();
        $dossier->setClient($client);

        $form = $this->createForm(DossierMedType::class, $dossier, [
            'client' => $client,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // donation must be owned by same client
            if ($dossier->getDon()?->getClient()?->getId() !== $client->getId()) {
                throw $this->createAccessDeniedException();
            }

            $em->persist($dossier);
            $em->flush();

            $this->addFlash('success', 'Medical dossier created.');
            return $this->redirectToRoute('front_dossier_show');
        }

        return $this->render('front/dossier/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front/dossier/{id}/edit', name: 'front_dossier_edit', methods: ['GET','POST'])]
    public function edit(DossierMed $dossier, Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($clientRepo);

        if ($dossier->getClient()?->getId() !== $client->getId()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(DossierMedType::class, $dossier, [
            'client' => $client,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($dossier->getDon()?->getClient()?->getId() !== $client->getId()) {
                throw $this->createAccessDeniedException();
            }

            $em->flush();
            $this->addFlash('success', 'Medical dossier updated.');
            return $this->redirectToRoute('front_dossier_show');
        }

        return $this->render('front/dossier/edit.html.twig', [
            'form' => $form->createView(),
            'dossier' => $dossier,
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
            $this->addFlash('success', 'Medical dossier deleted.');
        }

        return $this->redirectToRoute('front_dossier_show');
    }
}
