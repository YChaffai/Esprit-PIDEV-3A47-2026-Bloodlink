<?php

namespace App\Controller\Back;

use App\Entity\Demande;
use App\Entity\Stock;
use App\Entity\Transfert;
use App\Form\DemandeType;
use App\Repository\DemandeRepository;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/demande')]
class BackDemandeController extends AbstractController
{
    #[Route('/', name: 'back_demande_index', methods: ['GET'])]
    public function index(Request $request, DemandeRepository $demandeRepository): Response
    {
        $search = $request->query->get('search', '');
        $urgence = $request->query->get('urgence', '');
        $status = $request->query->get('status', '');
        
        $criteria = [
            'search' => $search,
            'urgence' => $urgence,
            'status' => $status
        ];

        $demandes = $demandeRepository->searchBy($criteria);

        if ($request->isXmlHttpRequest()) {
            return $this->render('back/demande/_demande_table.html.twig', [
                'demandes' => $demandes,
            ]);
        }

        return $this->render('back/Demande.html.twig', [
            'demandes' => $demandes,
            'search' => $search,
            'urgence' => $urgence,
            'status' => $status
        ]);
    }

    #[Route('/search', name: 'back_demande_search', methods: ['GET'])]
    public function search(Request $request, DemandeRepository $demandeRepository): JsonResponse
    {
        $query = $request->query->get('q', '');
        $demandes = $demandeRepository->search($query);

        $data = [];
        foreach ($demandes as $demande) {
            $data[] = [
                'id' => $demande->getId(),
                'banque' => $demande->getBanque()->getNom(),
                'typeSang' => $demande->getTypeSang(),
                'quantite' => $demande->getQuantite(),
                'urgence' => $demande->getUrgence(),
                'status' => $demande->getStatus(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/new', name: 'back_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $demande = new Demande();
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setStatus('EN_ATTENTE');
            $demande->setCreatedAt(new \DateTimeImmutable());
            $demande->setUpdatedAt(new \DateTimeImmutable());
            $demande->setClient($this->getUser());
            $em->persist($demande);
            $em->flush();

            $this->addFlash('success', 'Demande créée avec succès.');
            return $this->redirectToRoute('back_demande_index');
        }

        return $this->render('back/newDemande.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'back_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Demande modifiée avec succès.');
            return $this->redirectToRoute('back_demande_index');
        }

        return $this->render('back/editDemande.html.twig', [
            'demande' => $demande,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'back_demande_delete', methods: ['POST'])]
    public function delete(Request $request, Demande $demande, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demande->getId(), $request->request->get('_token'))) {
            $em->remove($demande);
            $em->flush();
            $this->addFlash('success', 'Demande supprimée avec succès.');
        }

        return $this->redirectToRoute('back_demande_index');
    }

    #[Route('/{id}/valider', name: 'back_demande_valider')]
    public function valider(Demande $demande, EntityManagerInterface $em, StockRepository $stockRepo): Response
    {
        // 1. Mark Demande as Validated
        $demande->setStatus('VALIDEE');
        $demande->setUpdatedAt(new \DateTimeImmutable());

        $banque = $demande->getBanque();
        
        // 2. Find or Create Stock for the Banque
        $stock = $stockRepo->findOneBy([
            'type_orgid' => $banque->getId(),
            'type_org' => 'banque',
            'type_sang' => $demande->getTypeSang()
        ]);

        if (!$stock) {
            $stock = new Stock();
            $stock->setTypeOrgid($banque->getId());
            $stock->setTypeOrg('banque');
            $stock->setTypeSang($demande->getTypeSang());
            $stock->setQuantite(0);
            $stock->setCreatedAt(new \DateTimeImmutable());
            $em->persist($stock);
        }

        // Update quantity (Add because they demanded/received blood)
        $stock->setQuantite($stock->getQuantite() + $demande->getQuantite());
        $stock->setUpdatedAt(new \DateTimeImmutable());

        // 3. Create Transfert Record
        $transfert = new Transfert();
        $transfert->setDemande($demande);
        $transfert->setStock($stock);
        $transfert->setFromOrgId(0); // 0 or Admin ID for Central/Headquarters
        $transfert->setFromOrg('BloodLink Central');
        $transfert->setToOrgId($banque->getId());
        $transfert->setToOrg($banque->getNom());
        $transfert->setQuantite($demande->getQuantite());
        $transfert->setStatus('EN_COURS'); // Transfert done/received
        $transfert->setDateEnvoie(new \DateTime());
        $transfert->setDateReception(new \DateTime());
        $transfert->setCreatedAt(new \DateTimeImmutable());
        $transfert->setUpdatedAt(new \DateTimeImmutable());

        $em->persist($transfert);
        
        $em->flush();

        $this->addFlash('success', 'Demande validée, stock mis à jour et transfert créé.');
        return $this->redirectToRoute('back_demande_index');
    }

    #[Route('/{id}/refuser', name: 'back_demande_refuser')]
    public function refuser(Demande $demande, EntityManagerInterface $em): Response
    {
        $demande->setStatus('REFUSEE');
        $demande->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        $this->addFlash('success', 'Demande refusée.');
        return $this->redirectToRoute('back_demande_index');
    }
}
