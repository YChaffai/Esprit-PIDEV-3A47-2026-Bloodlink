<?php

namespace App\Controller\Back;

use App\Entity\Don;
use App\Form\DonType;
use App\Repository\DonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/don')]
class DonBackController extends AbstractController
{
    #[Route('/', name: 'back_don_index', methods: ['GET'])]
    public function index(DonRepository $repo): Response
    {
        return $this->render('back/don/index.html.twig', [
            'dons' => $repo->findBy([], ['date' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'back_don_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $don = new Don();
        $form = $this->createForm(DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($don);
            $em->flush();

            $this->addFlash('success', 'Don ajouté avec succès.');
            return $this->redirectToRoute('back_don_index');
        }

        return $this->render('back/don/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'back_don_show', methods: ['GET'])]
    public function show(Don $don): Response
    {
        return $this->render('back/don/show.html.twig', [
            'don' => $don,
        ]);
    }

    #[Route('/{id}/edit', name: 'back_don_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Don $don, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Don modifié avec succès.');
            return $this->redirectToRoute('back_don_index');
        }

        return $this->render('back/don/edit.html.twig', [
            'form' => $form->createView(),
            'don' => $don,
        ]);
    }

    #[Route('/{id}/delete', name: 'back_don_delete', methods: ['POST'])]
    public function delete(Request $request, Don $don, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_don_'.$don->getId(), $request->request->get('_token'))) {
            $em->remove($don);
            $em->flush();
            $this->addFlash('success', 'Don supprimé.');
        }

        return $this->redirectToRoute('back_don_index');
    }
}
