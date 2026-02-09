<?php

namespace App\Controller\front;

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
final class DemandeController extends AbstractController
{
    #[Route('/', name: 'app_demande_index', methods: ['GET'])]
public function index(Request $request, DemandeRepository $repo): Response
{
    $keyword = $request->query->get('search'); // recherche
    $sortField = $request->query->get('sort', 'id'); // tri par défaut id
    $sortDir = $request->query->get('dir', 'ASC');   // direction par défaut ASC

    if ($keyword) {
        $demandes = $repo->search($keyword);
    } else {
        $demandes = $repo->sortBy($sortField, $sortDir);
    }

    return $this->render('demande/index.html.twig', [
        'demandes' => $demandes,
        'search' => $keyword,
        'sortField' => $sortField,
        'sortDir' => $sortDir,
    ]);
}

    #[Route('/new', name: 'app_demande_new', methods: ['GET', 'POST'])]
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

            return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demande/new.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_show', methods: ['GET'])]
public function show(?Demande $demande): Response
{
    if (!$demande) {
        $this->addFlash('danger', 'Demande introuvable !');
        return $this->redirectToRoute('app_demande_index');
    }

    return $this->render('demande/show.html.twig', [
        'demande' => $demande,
    ]);
}


    #[Route('/{id}/edit', name: 'app_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('demande/edit.html.twig', [
            'demande' => $demande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_demande_delete', methods: ['POST'])]
    public function delete(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($demande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/admin/demande/search', name: 'back_demande_search', methods: ['GET'])]
public function search(Request $request, DemandeRepository $repo): JsonResponse
{
    $keyword = $request->query->get('q');

    $demandes = $repo->createQueryBuilder('d')
        ->where('d.typeSang LIKE :kw')
        ->orWhere('d.status LIKE :kw')
        ->orWhere('d.idBanque LIKE :kw')
        ->setParameter('kw', '%'.$keyword.'%')
        ->getQuery()
        ->getResult();

    $data = [];

    foreach ($demandes as $demande) {
        $data[] = [
            'id' => $demande->getId(),
            'banque' => $demande->getIdBanque(),
            'type' => $demande->getTypeSang(),
            'quantite' => $demande->getQuantite(),
            'status' => $demande->getStatus(),
        ];
    }

    return new JsonResponse($data);
}
}
