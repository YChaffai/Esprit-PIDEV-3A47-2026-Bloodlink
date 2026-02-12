<?php

namespace App\Controller\Back;

use App\Entity\Transfert;
use App\Form\TransfertType;
use App\Repository\TransfertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/transfert')]
class BackTransfertController extends AbstractController
{
    #[Route('/', name: 'back_transfert_index', methods: ['GET'])]
    public function index(Request $request, TransfertRepository $repo): Response
    {
        $search = $request->query->get('search');
        $sort   = $request->query->get('sort', 'id');
        $dir    = $request->query->get('dir', 'ASC');

        $allowedFields = ['id', 'quantite', 'dateEnvoie', 'dateReception', 'status', 'toOrg'];
        if (!in_array($sort, $allowedFields)) {
            $sort = 'id';
        }

        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

        $qb = $repo->createQueryBuilder('t')
            ->leftJoin('t.demande', 'd')
            ->addSelect('d');

        if ($search) {
            $qb->andWhere('t.toOrg LIKE :search OR t.status LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $qb->orderBy('t.' . $sort, $dir);

        $transferts = $qb->getQuery()->getResult();

        return $this->render('back/Transfert.html.twig', [
            'transferts' => $transferts,
            'sort'       => $sort,
            'dir'        => $dir,
            'search'     => $search,
        ]);
    }

    #[Route('/search', name: 'back_transfert_search', methods: ['GET'])]
    public function search(Request $request, TransfertRepository $repo): JsonResponse
    {
        $q = $request->query->get('q');

        $qb = $repo->createQueryBuilder('t')
            ->leftJoin('t.demande', 'd')
            ->addSelect('d');

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
                'dateEnvoie'    => $t->getDateEnvoie()?->format('Y-m-d'),
                'dateReception' => $t->getDateReception()?->format('Y-m-d'),
                'status'        => $t->getStatus(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/{id}/edit', name: 'back_transfert_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Transfert $transfert, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TransfertType::class, $transfert);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transfert->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Transfert modifié avec succès.');
            return $this->redirectToRoute('back_transfert_index');
        }

        return $this->render('back/editTransfert.html.twig', [
            'form'      => $form,
            'transfert' => $transfert,
        ]);
    }

    #[Route('/delete/{id}', name: 'back_transfert_delete', methods: ['POST'])]
    public function delete(Request $request, Transfert $transfert, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $transfert->getId(), $request->request->get('_token'))) {
            $em->remove($transfert);
            $em->flush();
            $this->addFlash('success', 'Transfert supprimé.');
        }

        return $this->redirectToRoute('back_transfert_index');
    }
}
