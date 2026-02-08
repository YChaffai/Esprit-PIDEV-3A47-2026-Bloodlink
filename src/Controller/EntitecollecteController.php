<?php

namespace App\Controller;

use App\Entity\Entitecollecte;
use App\Form\EntitecollecteType;
use App\Repository\EntitecollecteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/entitecollecte')]
final class EntitecollecteController extends AbstractController
{
    #[Route(name: 'app_entitecollecte_index', methods: ['GET'])]
    public function index(EntitecollecteRepository $entitecollecteRepository): Response
    {
        return $this->render('entitecollecte/index.html.twig', [
            'entitecollectes' => $entitecollecteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_entitecollecte_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entitecollecte = new Entitecollecte();
        $form = $this->createForm(EntitecollecteType::class, $entitecollecte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($entitecollecte);
            $entityManager->flush();

            return $this->redirectToRoute('app_entitecollecte_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('entitecollecte/new.html.twig', [
            'entitecollecte' => $entitecollecte,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_entitecollecte_show', methods: ['GET'])]
    public function show(Entitecollecte $entitecollecte): Response
    {
        return $this->render('entitecollecte/show.html.twig', [
            'entitecollecte' => $entitecollecte,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_entitecollecte_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entitecollecte $entitecollecte, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntitecollecteType::class, $entitecollecte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_entitecollecte_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('entitecollecte/edit.html.twig', [
            'entitecollecte' => $entitecollecte,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_entitecollecte_delete', methods: ['POST'])]
    public function delete(Request $request, Entitecollecte $entitecollecte, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$entitecollecte->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($entitecollecte);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_entitecollecte_index', [], Response::HTTP_SEE_OTHER);
    }
}
