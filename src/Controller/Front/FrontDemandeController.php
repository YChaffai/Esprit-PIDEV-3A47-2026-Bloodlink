<?php

namespace App\Controller\Front;

use App\Entity\Demande;
use App\Form\DemandeType;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/demande')]
final class FrontDemandeController extends AbstractController
{
    #[Route('/', name: 'front_demande_index', methods: ['GET'])]
    public function index(Request $request, DemandeRepository $repo): Response
    {
        $keyword   = $request->query->get('search');
        $sortField = $request->query->get('sort', 'id');
        $sortDir   = $request->query->get('dir', 'ASC');

        if ($keyword) {
            $demandes = $repo->search($keyword);
        } else {
            $demandes = $repo->sortBy($sortField, $sortDir);
        }

        return $this->render('front/demande/index.html.twig', [
            'demandes'  => $demandes,
            'search'    => $keyword,
            'sortField' => $sortField,
            'sortDir'   => $sortDir,
        ]);
    }

    #[Route('/new', name: 'front_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $demande = new Demande();
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setStatus('EN_ATTENTE');
            $demande->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($demande);
            $entityManager->flush();

            $this->addFlash('success', 'Demande créée avec succès.');
            return $this->redirectToRoute('front_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/demande/new.html.twig', [
            'demande' => $demande,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: 'front_demande_show', methods: ['GET'])]
    public function show(?Demande $demande): Response
    {
        if (!$demande) {
            $this->addFlash('danger', 'Demande introuvable !');
            return $this->redirectToRoute('front_demande_index');
        }

        return $this->render('front/demande/show.html.twig', [
            'demande' => $demande,
        ]);
    }

    #[Route('/{id}/edit', name: 'front_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Demande mise à jour.');
            return $this->redirectToRoute('front_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/demande/edit.html.twig', [
            'demande' => $demande,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: 'front_demande_delete', methods: ['POST'])]
    public function delete(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $demande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($demande);
            $entityManager->flush();
            $this->addFlash('success', 'Demande supprimée.');
        }

        return $this->redirectToRoute('front_demande_index', [], Response::HTTP_SEE_OTHER);
    }
}
