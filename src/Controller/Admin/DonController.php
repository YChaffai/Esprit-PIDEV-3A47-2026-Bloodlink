<?php

namespace App\Controller\Admin;

use App\Entity\Don;
use App\Form\DonType;
use App\Repository\DonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/dons')]
class DonController extends AbstractController
{
    #[Route('', name: 'admin_don_index', methods: ['GET'])]
    public function index(DonRepository $repo): Response
    {
        return $this->render('admin/don/index.html.twig', [
            'dons' => $repo->findBy([], ['date' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'admin_don_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $don = new Don();
        $form = $this->createForm(DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($don->getIdEntite() === null) $don->setIdEntite(1);
            $em->persist($don);
            $em->flush();

            return $this->redirectToRoute('admin_don_index');
        }

        return $this->render('admin/don/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_don_show', methods: ['GET'])]
    public function show(Don $don): Response
    {
        return $this->render('admin/don/show.html.twig', [
            'don' => $don,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_don_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Don $don, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('admin_don_index');
        }

        return $this->render('admin/don/edit.html.twig', [
            'form' => $form->createView(),
            'don' => $don,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_don_delete', methods: ['POST'])]
    public function delete(Request $request, Don $don, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_don_'.$don->getId(), $request->request->get('_token'))) {
            $em->remove($don);
            $em->flush();
        }

        return $this->redirectToRoute('admin_don_index');
    }
}
