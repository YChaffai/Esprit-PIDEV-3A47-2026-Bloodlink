<?php
namespace App\Controller;

use App\Repository\BanqueRepository;
use App\Repository\EntiteCollecteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrgController extends AbstractController
{
    #[Route('/ajax/orgs', name: 'ajax_orgs', methods: ['GET'])]
    public function orgs(
        Request $request,
        BanqueRepository $banqueRepo,
        EntiteCollecteRepository $entiteRepo
    ): JsonResponse {
        $type = $request->query->get('type');

        $items = [];

        error_log("OrgController: Request received for type: " . $type);
        
        if ($type === 'banque') {
            $banques = $banqueRepo->findAll();
            error_log("OrgController: Found " . count($banques) . " banques.");
            foreach ($banques as $b) {
                $items[] = ['id' => $b->getId(), 'label' => $b->getNom()];
            }
        } elseif ($type === 'entitecollecte') {
            $entites = $entiteRepo->findAll();
            error_log("OrgController: Found " . count($entites) . " entites.");
            foreach ($entites as $e) {
                $items[] = ['id' => $e->getId(), 'label' => $e->getNom()];
            }
        }

        error_log("OrgController: Returning " . count($items) . " items.");

        return $this->json($items);
    }
}