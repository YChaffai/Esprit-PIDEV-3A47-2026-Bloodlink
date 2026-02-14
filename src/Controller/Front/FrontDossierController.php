<?php

namespace App\Controller\Front;

use App\Entity\DossierMed;
use App\Form\DossierMedType;
use App\Repository\DossierMedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontDossierController extends AbstractController
{
<<<<<<< HEAD
  private function currentClient()
  {
    /** @var \App\Entity\User $user */
    $user = $this->getUser();
    if (!$user) {
      throw $this->createAccessDeniedException('Vous devez être connecté.');
    }

    // Return client if user is a client, otherwise return null (Doctors/Admins don't have a linked Client entity usually)
    return $user->getClient();
  }
  #[Route('/front/dossier/new', name: 'front_dossier_new', methods: ['GET', 'POST'])]
  public function new(Request $request, EntityManagerInterface $em): Response
  {
    // Restriction: Only Doctors or Admins can create new dossiers from scratch
    $this->denyAccessUnlessGranted('ROLE_DOCTOR');

    $dossier = new DossierMed();
    $form = $this->createForm(DossierMedType::class, $dossier);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      // Auto-assign the client from the selected donation
      $don = $dossier->getDon();
      if ($don) {
        $dossier->setClient($don->getClient());
      }

      $em->persist($dossier);
      $em->flush();

      $this->addFlash('success', 'Dossier médical créé avec succès.');
      return $this->redirectToRoute('front_dossier_show');
    }

    return $this->render('front/dossier/new.html.twig', [
      'form' => $form->createView(),
    ]);
  }


  #[Route('/front/dossier', name: 'front_dossier_show', methods: ['GET'])]
  public function index(DossierMedRepository $repo, Request $request): Response
  {
    $client = $this->currentClient();

    // 1. Get parameters from URL (e.g. ?q=plasma&sort=date&dir=ASC)
    $search = $request->query->get('q');
    $sort = $request->query->get('sort', 'date'); // Default sort by date
    $direction = $request->query->get('dir', 'DESC'); // Default direction DESC

    // 2. Use custom repository method
    $dossiers = $repo->findByClientSearchAndSort($client, $search, $sort, $direction);

    return $this->render('front/dossier/index.html.twig', [
      'dossiers' => $dossiers,
      // Pass params back to view to keep form filled
      'currentSearch' => $search,
      'currentSort' => $sort,
      'currentDir' => $direction
    ]);
  }

  #[Route('/front/dossier/{id}/edit', name: 'front_dossier_edit', methods: ['GET', 'POST'])]
  public function edit(DossierMed $dossier, Request $request, EntityManagerInterface $em): Response
  {
    $client = $this->currentClient();

    // Security: Ownership check (Bypassed for Doctor/Admin)
    $isMedi = $this->isGranted('ROLE_DOCTOR') || $this->isGranted('ROLE_ADMIN');
    if (!$isMedi && $dossier->getClient()?->getId() !== $client?->getId()) {
      throw $this->createAccessDeniedException('Ce dossier ne vous appartient pas.');
    }
    $form = $this->createForm(DossierMedType::class, $dossier, [
      'client' => $client,
    ]);

    $form->remove('nom');
    $form->remove('prenom');
    $form->remove('typeSang');
    $form->remove('don');
    $form->remove('sexe'); // Assuming gender is also non-modifiable identity data

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $em->flush();
      $this->addFlash('success', 'Dossier mis à jour avec succès.');
      return $this->redirectToRoute('front_dossier_show');
    }

    return $this->render('front/dossier/edit.html.twig', [
      'form' => $form->createView(),
      'dossier' => $dossier, // Pass this to display the locked values as text
    ]);
  }

  #[Route('/front/dossier/{id}/delete', name: 'front_dossier_delete', methods: ['POST'])]
  public function delete(DossierMed $dossier, Request $request, EntityManagerInterface $em): Response
  {
    $client = $this->currentClient();

    $isMedi = $this->isGranted('ROLE_DOCTOR') || $this->isGranted('ROLE_ADMIN');
    if (!$isMedi && $dossier->getClient()?->getId() !== $client?->getId()) {
      throw $this->createAccessDeniedException();
    }

    if ($this->isCsrfTokenValid('delete_dossier_' . $dossier->getId(), (string)$request->request->get('_token'))) {
      $em->remove($dossier);
      $em->flush();
      $this->addFlash('success', 'Dossier médical supprimé.');
    }

    return $this->redirectToRoute('front_dossier_show');
  }
}

=======
    /**
     * Returns the logged user's linked Client entity if it exists, otherwise null.
     * No exceptions thrown here (so ADMIN/DOCTOR won't be blocked).
     */
    private function getClientOrNull(): ?\App\Entity\Client
    {
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
        if (!$user) {
            return null;
        }

        return method_exists($user, 'getClient') ? $user->getClient() : null;
    }

    #[Route('/front/dossier/new', name: 'front_dossier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Only Doctor/Admin can create dossiers from scratch
     if (!$this->isGranted('ROLE_DOCTOR') || $this->isGranted('ROLE_ADMIN')) {
    throw $this->createAccessDeniedException();
}


        $dossier = new DossierMed();
        $form = $this->createForm(DossierMedType::class, $dossier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Auto-assign client from selected donation
            $don = $dossier->getDon();
            if ($don && $don->getClient()) {
                $dossier->setClient($don->getClient());
            }

            $em->persist($dossier);
            $em->flush();

            $this->addFlash('success', 'Dossier médical créé avec succès.');
            return $this->redirectToRoute('front_dossier_show');
        }

        return $this->render('front/dossier/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front/dossier', name: 'front_dossier_show', methods: ['GET'])]
    public function index(DossierMedRepository $repo, Request $request): Response
    {
        // Must be logged in to view
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $isMedi = $this->isGranted('ROLE_DOCTOR') || $this->isGranted('ROLE_ADMIN');
        $client = $this->getClientOrNull();

        // If not admin/doctor, must be a client
        if (!$isMedi && !$client) {
            throw $this->createAccessDeniedException();
        }

        // Query params (?q=...&sort=...&dir=...)
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'date');
        $direction = strtoupper((string)$request->query->get('dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        // Build query (admin/doctor: all dossiers, client: only theirs)
        $qb = $repo->createQueryBuilder('dm')
            ->leftJoin('dm.don', 'don')->addSelect('don')
            ->leftJoin('dm.client', 'c')->addSelect('c')
            ->leftJoin('c.user', 'u')->addSelect('u');

        if (!$isMedi) {
            $qb->andWhere('dm.client = :client')->setParameter('client', $client);
        }

        if ($search) {
            $q = '%' . mb_strtolower($search) . '%';
            $qb->andWhere("
                LOWER(dm.nom) LIKE :q OR
                LOWER(dm.prenom) LIKE :q OR
                LOWER(dm.typeSang) LIKE :q OR
                LOWER(don.typeDon) LIKE :q
            ")->setParameter('q', $q);
        }

        // Allowlisted sort fields
        $sortMap = [
            'id' => 'dm.id',
            'nom' => 'dm.nom',
            'prenom' => 'dm.prenom',
            'age' => 'dm.age',
            'typeSang' => 'dm.typeSang',
            'date' => 'don.date',
        ];
        $orderBy = $sortMap[$sort] ?? 'don.date';

        $qb->orderBy($orderBy, $direction);

        $dossiers = $qb->getQuery()->getResult();

        return $this->render('front/dossier/index.html.twig', [
            'dossiers' => $dossiers,
            'currentSearch' => $search,
            'currentSort' => $sort,
            'currentDir' => $direction,
            'isMedi' => $isMedi,
        ]);
    }

    #[Route('/front/dossier/{id}/edit', name: 'front_dossier_edit', methods: ['GET', 'POST'])]
    public function edit(DossierMed $dossier, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $isMedi = $this->isGranted('ROLE_DOCTOR') || $this->isGranted('ROLE_ADMIN');
        $client = $this->getClientOrNull();

        if (!$isMedi && !$client) {
            throw $this->createAccessDeniedException();
        }

        // Ownership check (bypassed for Doctor/Admin)
        if (!$isMedi && $dossier->getClient()?->getId() !== $client?->getId()) {
            throw $this->createAccessDeniedException('Ce dossier ne vous appartient pas.');
        }

        $form = $this->createForm(DossierMedType::class, $dossier, [
            'client' => $client, // filter dons list to this client (if form uses it)
        ]);

        // Lock identity fields in edit
        $form->remove('nom');
        $form->remove('prenom');
        $form->remove('typeSang');
        $form->remove('don');
        $form->remove('sexe');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Dossier mis à jour avec succès.');
            return $this->redirectToRoute('front_dossier_show');
        }

        return $this->render('front/dossier/edit.html.twig', [
            'form' => $form->createView(),
            'dossier' => $dossier,
            'isMedi' => $isMedi,
        ]);
    }

    #[Route('/front/dossier/{id}/delete', name: 'front_dossier_delete', methods: ['POST'])]
    public function delete(DossierMed $dossier, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->getUser()) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $isMedi = $this->isGranted('ROLE_DOCTOR') || $this->isGranted('ROLE_ADMIN');
        $client = $this->getClientOrNull();

        if (!$isMedi && !$client) {
            throw $this->createAccessDeniedException();
        }

        // Ownership check (bypassed for Doctor/Admin)
        if (!$isMedi && $dossier->getClient()?->getId() !== $client?->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete_dossier_' . $dossier->getId(), (string) $request->request->get('_token'))) {
            $em->remove($dossier);
            $em->flush();
            $this->addFlash('success', 'Dossier médical supprimé.');
        }

        return $this->redirectToRoute('front_dossier_show');
    }
}
>>>>>>> e5190a8 (JEW)
