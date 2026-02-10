<?php

namespace App\Controller\back;

use App\Entity\Demande;
use App\Form\DemandeType;
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
    #[Route('/new', name: 'admin_demande_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $demande = new Demande();
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setCreatedAt(new \DateTimeImmutable());
            $demande->setStatus('EN_ATTENTE');
            $em->persist($demande);
            $em->flush();

            $this->addFlash('success', 'Demande créée avec succès !');
            return $this->redirectToRoute('back_demande_index');
        }

        return $this->render('admin/demande/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_demande_edit')]
    public function edit(Demande $demande, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Demande modifiée avec succès !');
            return $this->redirectToRoute(' back_demande_index');
        }

        return $this->render('admin/demande/edit.html.twig', [
            'form' => $form->createView(),
            'demande' => $demande
        ]);
    }

    #[Route('/{id}', name: 'admin_demande_show')]
    public function show(Demande $demande): Response
    {
        return $this->render('admin/demande/show.html.twig', compact('demande'));
    }

    #[Route('/{id}/delete', name: 'admin_demande_delete', methods:['POST'])]
    public function delete(Demande $demande, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demande->getId(), $request->request->get('_token'))) {
            $em->remove($demande);
            $em->flush();
            $this->addFlash('success', 'Demande supprimée !');
        }
        return $this->redirectToRoute('back_demande_index');
    }
}

