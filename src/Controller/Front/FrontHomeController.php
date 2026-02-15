<?php

namespace App\Controller\Front;

use App\Repository\CampagneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontHomeController extends AbstractController
{
    #[Route('/', name: 'front_home')]
    public function index(CampagneRepository $campagneRepository): Response
    {
        $user = $this->getUser();
        
        // If user is logged in as a client, show only compatible campaigns
        if ($user && method_exists($user, 'getClient') && $user->getClient()) {
            $campagnes = $campagneRepository->findCompatibleForClient($user->getClient());
            $campagnes = array_slice($campagnes, 0, 3);
        } else {
            // Guest or non-client view: Fetch a few active/upcoming campaigns
            $campagnes = $campagneRepository->findBy([], ['date_debut' => 'ASC'], 3);
        }

        return $this->render('front/home.html.twig', [
            'campagnes' => $campagnes
        ]);
    }
}
