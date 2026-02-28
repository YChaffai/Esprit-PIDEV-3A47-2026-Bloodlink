<?php
namespace App\Controller;

use App\Service\GoogleCalendarService;
use App\Repository\ClientRepository;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GoogleConnectController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connect(ClientRegistry $clientRegistry)
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect([
                'https://www.googleapis.com/auth/calendar.events', // Permission d'écriture
                'email',
                'profile'
            ], [
            'prompt' => 'select_account consent']);
    }
#[Route('/connect/google/check', name: 'connect_google_check')]
public function connectCheck(
    Request $request, 
    ClientRegistry $clientRegistry, 
    GoogleCalendarService $googleCalendarService,
    ClientRepository $clientRepository
) {
    $clientGoogle = $clientRegistry->getClient('google');
    $session = $request->getSession();
    $data = $session->get('pending_rdv_data');

    try {
        $accessToken = $clientGoogle->getAccessToken();
        
        // On vérifie que $data n'est pas NULL avant de l'utiliser
        if ($data && isset($data['date_don'])) {
            
            // Sécurité : S'assurer que la date est un objet DateTime
            $startDate = $data['date_don'];
            if (!$startDate instanceof \DateTime) {
                $startDate = new \DateTime($startDate['date']);
            }
            $endDate = (clone $startDate)->add(new \DateInterval('PT1H'));

            // Appel au service avec les clés exactes de ta session
            $googleCalendarService->addEvent(
                $accessToken->getToken(),
                '🩸 Don de sang - BloodLink',
                $data['entite_nom'] ?? 'Centre de collecte',
                'Confirmation de votre rendez-vous de don de sang.',
                $startDate,
                $endDate
            );
            
            $this->addFlash('success', 'Rendez-vous ajouté à votre calendrier !');
            $session->remove('pending_rdv_data'); // On nettoie après succès
        }
    } catch (\Exception $e) {
        $this->addFlash('error', 'Erreur lors de l\'ajout Google : ' . $e->getMessage());
    }

    // Redirection vers la liste (Calcul de l'ID pour éviter le crash int/string)
    $user = $this->getUser();
    $client = $clientRepository->findOneBy(['user' => $user]);
    $clientId = $client ? $client->getId() : ($data['client_id'] ?? 0);

    return $this->redirectToRoute('rendezvous_list', ['client_id' => (int)$clientId]);
}
}