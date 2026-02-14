<?php

namespace App\Controller\Back;

use App\Entity\DossierMed;
use App\Form\DossierMedType;
use App\Repository\DossierMedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/dossier')]
class DossierBackController extends AbstractController
{
    #[Route('/', name: 'back_dossier_index', methods: ['GET'])]
    public function index(DossierMedRepository $repo): Response
    {
        return $this->render('back/dossier/index.html.twig', [
            'dossiers' => $repo->findBy([], ['id' => 'DESC']),
        ]);
    }

    #[Route('/new', name: 'back_dossier_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $dossier = new DossierMed();

        $form = $this->createForm(DossierMedType::class, $dossier, [
            'client' => null,
        ]);
        $form->handleRequest($request);

        // ✅ SET CLIENT BEFORE VALIDATION
        if ($form->isSubmitted()) {
            $don = $form->get('don')->getData();
            if ($don && $don->getClient()) {
                $dossier->setClient($don->getClient());
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($dossier);
            $em->flush();

            $this->addFlash('success', 'Dossier médical ajouté avec succès.');
            return $this->redirectToRoute('back_dossier_index');
        }

        return $this->render('back/dossier/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'back_dossier_show', methods: ['GET'])]
    public function show(DossierMed $dossier): Response
    {
        return $this->render('back/dossier/show.html.twig', [
            'dossier' => $dossier,
        ]);
    }

    #[Route('/{id}/edit', name: 'back_dossier_edit', methods: ['GET','POST'])]
    public function edit(Request $request, DossierMed $dossier, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(DossierMedType::class, $dossier, [
            'client' => $dossier->getClient(), // filter dons list
        ]);
        $form->handleRequest($request);

        // ✅ SET CLIENT BEFORE VALIDATION
        if ($form->isSubmitted()) {
            $don = $form->get('don')->getData();
            if ($don && $don->getClient()) {
                $dossier->setClient($don->getClient());
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            // ✅ No persist needed on edit (already managed)
            $em->flush();

            $this->addFlash('success', 'Dossier médical modifié avec succès.');
            return $this->redirectToRoute('back_dossier_index');
        }

        return $this->render('back/dossier/edit.html.twig', [
            'form' => $form->createView(),
            'dossier' => $dossier,
        ]);
    }

    #[Route('/{id}/delete', name: 'back_dossier_delete', methods: ['POST'])]
    public function delete(Request $request, DossierMed $dossier, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_dossier_'.$dossier->getId(), $request->request->get('_token'))) {
            $em->remove($dossier);
            $em->flush();
            $this->addFlash('success', 'Dossier médical supprimé.');
        }

        return $this->redirectToRoute('back_dossier_index');
    }
}
