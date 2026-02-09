<?php
namespace App\Controller;

use App\Repository\BanqueRepository;
use App\Repository\EntitecollecteRepository;
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
        EntitecollecteRepository $entiteRepo
    ): JsonResponse {
        $type = $request->query->get('type');

        $items = [];

        if ($type === 'banque') {
            foreach ($banqueRepo->findAll() as $b) {
                $items[] = ['id' => $b->getId(), 'label' => $b->getNom()];
            }
        } elseif ($type === 'entitecollecte') {
            foreach ($entiteRepo->findAll() as $e) {
                $items[] = ['id' => $e->getId(), 'label' => $e->getNom()];
            }
        }

        return $this->json($items);
    }
}