<?php

namespace App\Controller\Front;

use App\Entity\Demande;
use App\Entity\Transfert;
use App\Repository\TransfertRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transfert')]
class FrontTransfertController extends AbstractController
{
    #[Route('/', name: 'front_transfert_index', methods: ['GET'])]
    public function index(Request $request, TransfertRepository $repo): Response
    {
        $sortField = $request->query->get('sort', 'id');
        $sortDir   = $request->query->get('dir', 'ASC');

        $allowedFields = ['id', 'quantite', 'dateEnvoie', 'dateReception', 'status'];
        if (!in_array($sortField, $allowedFields)) {
            $sortField = 'id';
        }

        $sortDir = strtoupper($sortDir) === 'DESC' ? 'DESC' : 'ASC';

        $qb = $repo->createQueryBuilder('t')
            ->leftJoin('t.demande', 'd')
            ->addSelect('d')
            ->orderBy('t.' . $sortField, $sortDir);

        $transferts = $qb->getQuery()->getResult();

        return $this->render('front/transfert/index.html.twig', [
            'transferts' => $transferts,
            'sortField'  => $sortField,
            'sortDir'    => $sortDir,
        ]);
    }

    #[Route('/search', name: 'front_transfert_search', methods: ['GET'])]
    public function search(Request $request, TransfertRepository $repo): JsonResponse
    {
        $q = $request->query->get('q');

        $qb = $repo->createQueryBuilder('t');

        if ($q) {
            $qb->where('t.toOrg LIKE :q')
               ->orWhere('t.status LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        $transferts = $qb->getQuery()->getResult();

        $data = [];
        foreach ($transferts as $t) {
            $data[] = [
                'id'            => $t->getId(),
                'demande'       => $t->getDemande()?->getId(),
                'toOrg'         => $t->getToOrg(),
                'quantite'      => $t->getQuantite(),
                'status'        => $t->getStatus(),
                'dateEnvoie'    => $t->getDateEnvoie()?->format('d/m/Y'),
                'dateReception' => $t->getDateReception()?->format('d/m/Y'),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/{id}', name: 'front_transfert_show', methods: ['GET'])]
    public function show($id, TransfertRepository $repo): Response
    {
        $transfert = $repo->find($id);

        return $this->render('front/transfert/show.html.twig', [
            'transfert' => $transfert,
        ]);
    }

    #[Route('/demande/{id}', name: 'front_transfert_by_demande')]
    public function byDemande(Demande $demande, TransfertRepository $repo): Response
    {
        $transferts = $repo->findBy(['demande' => $demande]);

        return $this->render('front/transfert/index.html.twig', [
            'transferts' => $transferts,
            'sortField'  => 'id',
            'sortDir'    => 'ASC',
        ]);
    }
    #[Route('/agent/transfert/{id}/valider', name: 'front_transfert_valider')]
public function validerReception(Transfert $transfert, EntityManagerInterface $em): Response
{
    if ($transfert->getStatus() !== 'EN_COURS') {
        $this->addFlash('danger', 'Ce transfert ne peut pas être validé.');
        return $this->redirectToRoute('front_transfert_index');
    }

    // ✅ Changer statut transfert
    $transfert->setStatus('RECU');

    // ✅ Changer statut demande liée
    if ($transfert->getDemande()) {
        $transfert->getDemande()->setStatus('VALIDEE');
        $transfert->getDemande()->setUpdatedAt(new \DateTimeImmutable());
    }

    $em->flush();

    $this->addFlash('success', 'Réception validée avec succès.');

    return $this->redirectToRoute('front_transfert_index');
}
    
}
