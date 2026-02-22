<?php

namespace App\Controller\backController;

use App\Entity\Compagne;
use App\Form\CompagneType;
use App\Repository\CompagneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\DonorPredictionService;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/back/compagne')]
final class CompagneBackController extends AbstractController
{
    // Afficher la liste des campagnes (Read)
    #[Route('/', name: 'back_compagne_index', methods: ['GET'])]
    public function index(Request $request, CompagneRepository $compagneRepository, PaginatorInterface $paginator): Response
    {
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'id');
        $direction = $request->query->get('direction', 'ASC');

        $query = $compagneRepository->findBySearchAndSort($search, $sort, $direction);

        // Paginate the results (5 per page)
        // Disable KnpPaginator's built-in sorting (we handle sorting in the repository)
        $compagnes = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            5,
            [
                PaginatorInterface::SORT_FIELD_PARAMETER_NAME => 'knp_sort',
                PaginatorInterface::SORT_DIRECTION_PARAMETER_NAME => 'knp_direction',
            ]
        );

        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return $this->render('back/compagne/_table.html.twig', [
                'compagnes' => $compagnes,
            ]);
        }

        return $this->render('back/compagne/index.html.twig', [
            'compagnes' => $compagnes,
            'currentSearch' => $search,
            'currentSort' => $sort,
            'currentDirection' => $direction,
        ]);
    }

    // Créer une campagne (Create)
    #[Route('/new', name: 'back_compagne_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $compagne = new Compagne();
        $form = $this->createForm(CompagneType::class, $compagne);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($compagne);
            $entityManager->flush();

            $this->addFlash('success', 'La campagne a été créée avec succès.');

            // Redirection vers la prédiction
            return $this->redirectToRoute('back_compagne_predict', ['id' => $compagne->getId()]);
        }

        return $this->render('back/compagne/new.html.twig', [
            'compagne' => $compagne,
            'form' => $form->createView(),
        ]);
    }

    // Modifier une campagne (Update)
    #[Route('/{id}/edit', name: 'back_compagne_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Compagne $compagne, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompagneType::class, $compagne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La campagne a été modifiée avec succès.');

            return $this->redirectToRoute('back_compagne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/compagne/edit.html.twig', [
            'compagne' => $compagne,
            'form' => $form,
        ]);
    }

    // Supprimer une campagne (Delete)
    #[Route('/{id}', name: 'back_compagne_delete', methods: ['POST'])]
    public function delete(Request $request, Compagne $compagne, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($compagne);
        $entityManager->flush();
        
        $this->addFlash('success', 'La campagne a été supprimée avec succès.');

        return $this->redirectToRoute('back_compagne_index', [], Response::HTTP_SEE_OTHER);
    }

    
    #[Route('/predict/{id}', name: 'back_compagne_predict', methods: ['GET'])]
    public function predict(Compagne $compagne, DonorPredictionService $predictionService): Response
    {
        $result = $predictionService->predictNextCampaignDonors([
            'date_debut' => $compagne->getDateDebut()->format('Y-m-d'),
            'date_fin' => $compagne->getDateFin()->format('Y-m-d'),
            'titre' => $compagne->getTitre(),
        ]);
        return $this->render('back/compagne/prediction.html.twig', [
            'prediction' => $result['prediction'],
            'explanation' => $result['explanation'],
            /////chart
            'chartUrl' => $result['chartUrl'],
            /////chart
        ]);
    }
}
