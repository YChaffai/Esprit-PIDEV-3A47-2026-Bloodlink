<?php

namespace App\Controller;

use App\Repository\CampagneRepository;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CampagneController extends AbstractController
{
    #[Route('/campagne/list', name:'campagne_list')]
    public function list(CampagneRepository $campagneRepository, ClientRepository $clientRepository){
        // return $this->render('campagne/list.html.twig', [
        //     'campagne' => $campagneRepository->findAll()
        // ]);

         // 1. Simulation du client connecté (ID 4 par exemple pour le dév)
        // Plus tard, vous remplacerez ceci par $this->getUser()
        $clientId = 1;
        $client = $clientRepository->find($clientId);

        if ($client) {
            // 2. Utilisation de votre méthode personnalisée du Repository
            // Elle filtre par Type de Sang ET vérifie la date du dernier don (+3 semaines)
            $campagnes = $campagneRepository->findCompatibleForClient($client);
        } else {
            // Fallback : Si l'ID 4 n'existe pas, on affiche tout pour éviter une page vide
            $campagnes = $campagneRepository->findAll();
        }

        return $this->render('campagne/list.html.twig', [
            'campagne' => $campagnes,
            'client_id' => $clientId // Nécessaire pour vos liens "Participer" dans la vue
        ]);
    }

}
