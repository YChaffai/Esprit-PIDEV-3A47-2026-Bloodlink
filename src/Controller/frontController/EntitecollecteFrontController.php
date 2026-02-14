<?php

namespace App\Controller\frontController;

use App\Entity\EntiteCollecte;
use App\Form\EntiteCollecteType;
use App\Repository\EntiteCollecteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('front/entitecollecte')]
final class EntitecollecteFrontController extends AbstractController
{
    // Afficher la liste des entités de collecte
    #[Route(name: 'app_entitecollecte_index', methods: ['GET'])]
    public function index(Request $request, EntiteCollecteRepository $entitecollecteRepository): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'nom'); // Default to nom
        $direction = $request->query->get('direction', 'ASC');

        // Prevent sorting by ID in front-office as requested
        if ($sort === 'id') {
            $sort = 'nom';
        }

        $entites = $entitecollecteRepository->findBySearchAndSort($search, $sort, $direction);

        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return $this->render('front/entitecollecte/_table.html.twig', [
                'entitecollectes' => $entites,
            ]);
        }

        return $this->render('front/entitecollecte/index.html.twig', [
            'entitecollectes' => $entites,
            'currentSearch' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction,
        ]);
    }

    // Créer une nouvelle entité de collecte
    #[Route('/new', name: 'app_entitecollecte_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $entitecollecte = new EntiteCollecte();
        $form = $this->createForm(EntiteCollecteType::class, $entitecollecte);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($entitecollecte);
            $entityManager->flush();

            $this->addFlash('success', 'L\'entité de collecte a été ajoutée avec succès.');
            return $this->redirectToRoute('app_entitecollecte_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/entitecollecte/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Afficher une entité de collecte spécifique
    #[Route('/{id}', name: 'app_entitecollecte_show', methods: ['GET'])]
    public function show(EntiteCollecte $entitecollecte): Response
    {
        return $this->render('front/entitecollecte/show.html.twig', [
            'entitecollecte' => $entitecollecte,
        ]);
    }

    // Modifier une entité de collecte existante
    #[Route('/{id}/edit', name: 'app_entitecollecte_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntiteCollecte $entitecollecte, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EntiteCollecteType::class, $entitecollecte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L\'entité de collecte a été modifiée avec succès.');
            return $this->redirectToRoute('app_entitecollecte_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/entitecollecte/edit.html.twig', [
            'entitecollecte' => $entitecollecte,
            'form' => $form->createView(),
        ]);
    }

    // Supprimer une entité de collecte
    #[Route('/{id}', name: 'app_entitecollecte_delete', methods: ['POST'])]
    public function delete(Request $request, EntiteCollecte $entitecollecte, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($entitecollecte);
        $entityManager->flush();
        
        $this->addFlash('success', 'L\'entité de collecte a été supprimée avec succès.');

        return $this->redirectToRoute('app_entitecollecte_index', [], Response::HTTP_SEE_OTHER);
    }
}
