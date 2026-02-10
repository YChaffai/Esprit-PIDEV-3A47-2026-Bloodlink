<?php

namespace App\Controller\back;

use App\Entity\Demande;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/demande')]
class DemandeController extends AbstractController
{
    #[Route('/', name: 'back_demande_index')]
    public function index(Request $request,DemandeRepository $repo): Response
    {
       $search = $request->query->get('search', '');
    $sort   = $request->query->get('sort', 'id');
    $dir    = $request->query->get('dir', 'ASC');

    $allowedFields = ['id', 'idBanque', 'typeSang', 'quantite', 'urgence', 'status'];

    if (!in_array($sort, $allowedFields)) {
        $sort = 'id';
    }

    $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';

    $qb = $repo->createQueryBuilder('d');

    if ($search) {
        $qb->andWhere('d.typeSang LIKE :search OR d.status LIKE :search')
           ->setParameter('search', '%' . $search . '%');
    }

    $qb->orderBy('d.' . $sort, $dir);

    return $this->render('back/index.html.twig', [
        'demandes' => $qb->getQuery()->getResult(),
        'search'   => $search,
        'sort'     => $sort,
        'dir'      => $dir,
    ]);
    }

    #[Route('/{id}/valider', name: 'back_demande_valider')]
    public function valider(Demande $demande, EntityManagerInterface $em): Response
    {
        $demande->setStatus('VALIDEE');
        $em->flush();

        $this->addFlash('success', 'Demande validée');

        return $this->redirectToRoute('back_demande_index');
    }

    #[Route('/{id}/refuser', name: 'back_demande_refuser')]
    public function refuser(Demande $demande, EntityManagerInterface $em): Response
    {
        $demande->setStatus('REFUSEE');
        $em->flush();

        $this->addFlash('danger', 'Demande refusée');

        return $this->redirectToRoute('back_demande_index');
    }
}

