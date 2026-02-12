<?php

namespace App\Controller\backController;

use App\Entity\Entitecollecte;
use App\Form\EntitecollecteType;
use App\Repository\EntitecollecteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/back/entitecollecte')]
final class EntitecollecteBackController extends AbstractController
{
    // Afficher la liste des entités de collecte (Read)
    #[Route('/', name: 'back_entitecollecte_index', methods: ['GET'])]
    public function index(Request $request, EntitecollecteRepository $entitecollecteRepository): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'ASC');

        $entites = $entitecollecteRepository->findBySearchAndSort($search, $sort, $direction);

        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return $this->render('back/entitecollecte/_table.html.twig', [
                'entitecollectes' => $entites,
            ]);
        }

        return $this->render('back/entitecollecte/index.html.twig', [
            'entitecollectes' => $entites,
            'currentSearch' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction,
        ]);
    }

    // Créer une entité de collecte (Create)
    #[Route('/new', name: 'back_entitecollecte_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entitecollecte = new Entitecollecte();
        $form = $this->createForm(EntitecollecteType::class, $entitecollecte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($entitecollecte);
            $entityManager->flush();

            $this->addFlash('success', 'L\'entité de collecte a été créée avec succès.');

            return $this->redirectToRoute('back_entitecollecte_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/entitecollecte/new.html.twig', [
            'entitecollecte' => $entitecollecte,
            'form' => $form->createView(),
        ]);
    }

    // Modifier une entité de collecte (Update)
    #[Route('/{id}/edit', name: 'back_entitecollecte_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Entitecollecte $entitecollecte, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntitecollecteType::class, $entitecollecte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'entité de collecte a été modifiée avec succès.');
            return $this->redirectToRoute('back_entitecollecte_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/entitecollecte/edit.html.twig', [
            'entitecollecte' => $entitecollecte,
            'form' => $form->createView(),
        ]);
    }

    // Supprimer une entité de collecte (Delete)
    #[Route('/{id}', name: 'back_entitecollecte_delete', methods: ['POST'])]
    public function delete(Request $request, Entitecollecte $entitecollecte, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($entitecollecte);
        $entityManager->flush();
        
        $this->addFlash('success', 'L\'entité de collecte a été supprimée avec succès.');

        return $this->redirectToRoute('back_entitecollecte_index', [], Response::HTTP_SEE_OTHER);
    }
}
