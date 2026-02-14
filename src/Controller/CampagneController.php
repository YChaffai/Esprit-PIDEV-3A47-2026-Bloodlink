<?php

namespace App\Controller;

use App\Repository\CompagneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/compagne')]
#[IsGranted('ROLE_CLIENT')]
final class compagneController extends AbstractController
{
  #[Route('/list', name: 'compagne_list')]
  public function list(compagneRepository $compagneRepository): Response
  {
    /** @var User $user */
    $user = $this->getUser();

    $client = $user->getClient();

    if (!$client) {
      throw $this->createAccessDeniedException('Client profile not completed.');
    }

    $compagnes = $compagneRepository->findCompatibleForClient($client);

    return $this->render('campagne/list.html.twig', [
      'compagne' => $compagnes,
      'client' => $client,
    ]);
  }
}

