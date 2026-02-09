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
    public function index(Request $request, CompagneRepository $compagneRepository): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'ASC');

        return $this->render('compagne/index.html.twig', [
            'compagnes' => $compagneRepository->findBySearchAndSort($search, $sort, $direction),
            'currentSearch' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction,
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

            $this->addFlash('success', 'La campagne a été créée avec succès.');

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

            $this->addFlash('success', 'La campagne a été modifiée avec succès.');

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
        $entityManager->remove($compagne);
        $entityManager->flush();
        
        $this->addFlash('success', 'La campagne a été supprimée avec succès.');

        return $this->redirectToRoute('app_compagne_index', [], Response::HTTP_SEE_OTHER);
    }
}
