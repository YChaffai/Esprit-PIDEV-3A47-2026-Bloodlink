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

#[Route('/front')]
class FrontController extends AbstractController
{
    #[Route('/', name: 'front_index')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        CompagneRepository $compagneRepository,
        EntitecollecteRepository $entiteRepository
    ): Response
    {
        // Formulaire création campagne
        $compagne = new Compagne();
        $compagneForm = $this->createForm(CompagneType::class, $compagne);
        $compagneForm->handleRequest($request);
        if ($compagneForm->isSubmitted() && $compagneForm->isValid()) {
            $compagne->setCreatedAt(new \DateTime());
            $em->persist($compagne);
            $em->flush();
            return $this->redirectToRoute('front_index');
        }

        // Formulaire inscription entité
        $entite = new Entitecollecte();
        $entiteForm = $this->createForm(EntitecollecteType::class, $entite);
        $entiteForm->handleRequest($request);
        if ($entiteForm->isSubmitted() && $entiteForm->isValid()) {
            $compagneId = $request->request->get('compagne_id');
            if ($compagneId) {
                $compagneSelect = $compagneRepository->find($compagneId);
                $compagneSelect->setEntite($entite);
            }
            $em->persist($entite);
            $em->flush();
            return $this->redirectToRoute('front_index');
        }

        return $this->render('front/index.html.twig', [
            'compagneForm' => $compagneForm->createView(),
            'entiteForm' => $entiteForm->createView(),
            'campagnes' => $compagneRepository->findAll(),
            'entites' => $entiteRepository->findAll(),
        ]);
    }
}
