<?php // src/Controller/Admin/DemandeController.php
namespace App\Controller\demandeadmin;

use App\Entity\Demande;
use App\Form\DemandeType;
use App\Repository\DemandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/demande')]
class demandeadmin extends AbstractController
{
    #[Route('/', name: 'admin_demande_index')]
    public function index(DemandeRepository $repo): Response
    {
        $demandes = $repo->findAll();
        return $this->render('admin/demande/index.html.twig', compact('demandes'));
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
            return $this->redirectToRoute('admin_demande_index');
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
            return $this->redirectToRoute('admin_demande_index');
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
        return $this->redirectToRoute('admin_demande_index');
    }
}
