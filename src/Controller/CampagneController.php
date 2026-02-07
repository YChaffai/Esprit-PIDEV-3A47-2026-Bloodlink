<?php

namespace App\Controller;

use App\Repository\CampagneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CampagneController extends AbstractController
{
    #[Route('/campagne/list', name:'campagne_list')]
    public function list(CampagneRepository $campagneRepository){
        return $this->render('campagne/list.html.twig', [
            'campagne' => $campagneRepository->findAll()
        ]);
    }

}
