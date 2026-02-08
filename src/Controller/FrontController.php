<?php

namespace App\Controller;

use App\Entity\Compagne;
use App\Entity\Entitecollecte;
use App\Form\CompagneType;
use App\Form\EntitecollecteType;
use App\Repository\CompagneRepository;
use App\Repository\EntitecollecteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/front')]
class FrontController extends AbstractController
{
    #[Route('/', name: 'front_index')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        CompagneRepository $compagneRepository,
        EntitecollecteRepository $entiteRepository
    ): Response {
        // Formulaire création campagne
        $compagne = new Compagne();
        $compagneForm = $this->createForm(CompagneType::class, $compagne);
        $compagneForm->handleRequest($request);
        if ($compagneForm->isSubmitted() && $compagneForm->isValid()) {
            $em->persist($compagne);
            $em->flush();
            return $this->redirectToRoute('front_index');
        }

        // Formulaire création entité
        $entite = new Entitecollecte();
        $entiteForm = $this->createForm(EntitecollecteType::class, $entite);
        $entiteForm->handleRequest($request);
        if ($entiteForm->isSubmitted() && $entiteForm->isValid()) {
            $em->persist($entite);
            $em->flush();
            return $this->redirectToRoute('front_index');
        }

        return $this->render('front/index.html.twig', [
            'compagneForm' => $compagneForm->createView(),
            'entiteForm' => $entiteForm->createView(),
            'campagnes' => $compagneRepository->findAll(),
            'entites' => $entiteRepository->findAll(),
        ]);
    }

    #[Route('/search', name: 'front_search', methods: ['GET'])]
    public function search(
        Request $request,
        CompagneRepository $compagneRepo,
        EntitecollecteRepository $entiteRepo
    ): JsonResponse {
        $qCampagne = $request->query->get('qCampagne', '');
        $sortCampagne = $request->query->get('sortCampagne', 'id');

        $qEntite = $request->query->get('qEntite', '');
        $sortEntite = $request->query->get('sortEntite', 'id');

        // Compagnes
        $campagnes = $compagneRepo->createQueryBuilder('c')
            ->leftJoin('c.entite', 'e')
            ->where('c.titre LIKE :q OR c.description LIKE :q')
            ->setParameter('q', "%$qCampagne%")
            ->orderBy("c.$sortCampagne", "ASC")
            ->getQuery()
            ->getResult();

        // Entités
        $entites = $entiteRepo->createQueryBuilder('e')
            ->where('e.nom LIKE :q OR e.localisation LIKE :q')
            ->setParameter('q', "%$qEntite%")
            ->orderBy("e.$sortEntite", "ASC")
            ->getQuery()
            ->getResult();

        $campagnesData = [];
        foreach ($campagnes as $c) {
            $campagnesData[] = [
                'id' => $c->getId(),
                'id_entite' => $c->getEntite()?->getId(),
                'titre' => $c->getTitre(),
                'description' => $c->getDescription(),
                'date_debut' => $c->getDateDebut()?->format('Y-m-d'),
                'date_fin' => $c->getDateFin()?->format('Y-m-d'),
            ];
        }

        $entitesData = [];
        foreach ($entites as $e) {
            $entitesData[] = [
                'id' => $e->getId(),
                'nom' => $e->getNom(),
                'localisation' => $e->getLocalisation(),
                'telephone' => $e->getTelephone(),
            ];
        }

        return new JsonResponse([
            'campagnes' => $campagnesData,
            'entites' => $entitesData,
        ]);
    }
}
