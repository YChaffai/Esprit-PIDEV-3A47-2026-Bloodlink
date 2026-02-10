<?php

namespace App\Controller\Back;

use App\Entity\EntiteCollecte;
use App\Form\EntiteCollecteType;
use App\Repository\EntiteCollecteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/entite_collecte')]
class EntiteCollecteController extends AbstractController
{
    #[Route('', name: 'back_entite_collecte_index', methods: ['GET'])]
    public function index(EntiteCollecteRepository $repository): Response
    {
        return $this->render('back/EntiteCollecte.html.twig', [
            'entite_collectes' => $repository->findAll(),
        ]);
    }

    #[Route('/new', name: 'back_entite_collecte_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $entite = new EntiteCollecte();
        $form = $this->createForm(EntiteCollecteType::class, $entite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($entite);
            $em->flush();

            $this->addFlash('success', 'Entité créée avec succès.');
            return $this->redirectToRoute('back_entite_collecte_index');
        }

        return $this->render('back/AjoutEntiteCollecte.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'back_entite_collecte_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, EntiteCollecte $entite, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(EntiteCollecteType::class, $entite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Entité mise à jour.');
            return $this->redirectToRoute('back_entite_collecte_index');
        }

        return $this->render('back/editEntiteCollecte.html.twig', [
            'entite_collecte' => $entite,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'back_entite_collecte_delete', methods: ['POST'])]
    public function delete(Request $request, EntiteCollecte $entite, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$entite->getId(), $request->request->get('_token'))) {
            $em->remove($entite);
            $em->flush();
            $this->addFlash('success', 'Entité supprimée.');
        }

        return $this->redirectToRoute('back_entite_collecte_index');
    }
}
