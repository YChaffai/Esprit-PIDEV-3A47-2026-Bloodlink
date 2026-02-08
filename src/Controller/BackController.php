<?php
namespace App\Controller;

use App\Entity\Compagne;
use App\Entity\Entitecollecte;
use App\Form\CompagneType;
use App\Form\EntitecollecteType;
use App\Repository\CompagneRepository;
use App\Repository\EntitecollecteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class BackController extends AbstractController
{
    #[Route('/', name: 'back_index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        CompagneRepository $compagneRepo,
        EntitecollecteRepository $entiteRepo
    ): Response {

        // --- Formulaires pour modification (optionnel) ---
        $newCompagne = new Compagne();
        $compagneForm = $this->createForm(CompagneType::class, $newCompagne);
        $compagneForm->handleRequest($request);

        if ($compagneForm->isSubmitted() && $compagneForm->isValid()) {
            $em->persist($newCompagne);
            $em->flush();
            return $this->redirectToRoute('back_index');
        }

        $newEntite = new Entitecollecte();
        $entiteForm = $this->createForm(EntitecollecteType::class, $newEntite);
        $entiteForm->handleRequest($request);

        if ($entiteForm->isSubmitted() && $entiteForm->isValid()) {
            $em->persist($newEntite);
            $em->flush();
            return $this->redirectToRoute('back_index');
        }

        return $this->render('back/index.html.twig', [
            'compagnes' => $compagneRepo->findAll(),
            'entites' => $entiteRepo->findAll(),
            'compagneForm' => $compagneForm->createView(),
            'entiteForm' => $entiteForm->createView(),
        ]);
    }

    // --- Suppression d'une campagne ---
    #[Route('/compagne/delete/{id}', name: 'back_delete_compagne', methods: ['POST'])]
    public function deleteCompagne(Request $request, Compagne $compagne, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_compagne'.$compagne->getId(), $request->request->get('_token'))) {
            $em->remove($compagne);
            $em->flush();
        }
        return $this->redirectToRoute('back_index');
    }

    // --- Suppression d'une entité ---
    #[Route('/entite/delete/{id}', name: 'back_delete_entite', methods: ['POST'])]
    public function deleteEntite(Request $request, Entitecollecte $entite, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_entite'.$entite->getId(), $request->request->get('_token'))) {
            $em->remove($entite);
            $em->flush();
        }
        return $this->redirectToRoute('back_index');
    }
}
