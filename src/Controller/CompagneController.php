<?php

namespace App\Controller;

use App\Entity\Compagne;
use App\Form\CompagneType;
use App\Repository\CompagneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/compagne')]
final class CompagneController extends AbstractController
{
    #[Route(name: 'app_compagne_index', methods: ['GET'])]
    public function index(CompagneRepository $compagneRepository): Response
    {
        return $this->render('compagne/index.html.twig', [
            'compagnes' => $compagneRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_compagne_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $compagne = new Compagne();
        $form = $this->createForm(CompagneType::class, $compagne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($compagne);
            $entityManager->flush();

            return $this->redirectToRoute('app_compagne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('compagne/new.html.twig', [
            'compagne' => $compagne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_compagne_show', methods: ['GET'])]
    public function show(Compagne $compagne): Response
    {
        return $this->render('compagne/show.html.twig', [
            'compagne' => $compagne,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_compagne_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Compagne $compagne, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompagneType::class, $compagne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_compagne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('compagne/edit.html.twig', [
            'compagne' => $compagne,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_compagne_delete', methods: ['POST'])]
    public function delete(Request $request, Compagne $compagne, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$compagne->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($compagne);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_compagne_index', [], Response::HTTP_SEE_OTHER);
    }
}
