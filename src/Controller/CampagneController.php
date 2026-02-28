<?php

namespace App\Controller;

use App\Repository\CompagneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/campagne')]
#[IsGranted('ROLE_CLIENT')]
final class CampagneController extends AbstractController
{
  #[Route('/list', name: 'campagne_list')]
  public function list(CompagneRepository $campagneRepository): Response
  {
    /** @var User $user */
    $user = $this->getUser();

    $client = $user->getClient();

    if (!$client) {
      throw $this->createAccessDeniedException('Client profile not completed.');
    }

    $campagnes = $campagneRepository->findCompatibleForClient($client);

    return $this->render('campagne/list.html.twig', [
      'campagne' => $campagnes,
      'client' => $client,
    ]);
  }
}
