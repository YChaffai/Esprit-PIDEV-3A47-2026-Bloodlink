<?php

namespace App\Controller\Front;

use App\Entity\Don;
use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Repository\DonRepository;
use App\Service\DonationEligibilityService;
use App\Service\DonationAiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FrontDonController extends AbstractController
{
    /**
     * ✅ HELPER: ROBUST CLIENT DETECTION
     * Matches the logic used in FrontDossierController for consistency.
     */
    private function currentClient(Request $request, ClientRepository $clientRepo): ?Client
    {
        $user = $this->getUser();
        if (!$user) {
            return null;
        }

        // 1. Check Real DB Link (Direct relation User -> Client)
        if (method_exists($user, 'getClient') && $user->getClient()) {
            return $user->getClient();
        }

        // 2. Check Session Fallback
        $session = $request->getSession();
        $clientId = $session ? $session->get('client_id') : null;
        if ($clientId) {
            return $clientRepo->find($clientId);
        }

        // 3. Admin Demo Mode (Optional: allows admin to see some data if needed)
        if ($this->isGranted('ROLE_ADMIN')) {
            return $clientRepo->findOneBy([]); 
        }

        return null;
    }

   #[Route('/front/don/new', name: 'front_don_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($request, $clientRepo);

        if (!$client) {
            $this->addFlash('danger', "Impossible de créer un don : votre compte n'est pas lié à un donneur.");
            return $this->redirectToRoute('front_don_index');
        }

        $don = new Don();
        
        // 🔥 INJECTION FORCEE AVANT LA VALIDATION
        $don->setClient($client);
        $don->setDate(new \DateTime());
        $don->setIdEntite(1); 

        $form = $this->createForm(\App\Form\DonType::class, $don);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($don);
            
            // Met à jour la date du dernier don du client
            $client->setDernierDon($don->getDate());
            $em->persist($client);
            
            $em->flush();

            $this->addFlash('success', 'Votre don a été enregistré avec succès !');
            return $this->redirectToRoute('front_don_index');
        }

        return $this->render('front/don/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front/don', name: 'front_don_index', methods: ['GET'])]
    public function index(
        DonRepository $donRepo, 
        Request $request, 
        ClientRepository $clientRepo,
        DonationAiService $aiService 
    ): Response
    {
        // 1. Force Login Check
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Veuillez vous connecter pour voir votre historique de dons.');
            return $this->redirectToRoute('app_login');
        }

        $client = $this->currentClient($request, $clientRepo);
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        // Search & Sort Parameters
        $search = $request->query->get('q');
        $sort = $request->query->get('sort', 'date');
        $direction = $request->query->get('dir', 'DESC');

        $dons = [];
        $recommendation = null;

        // 2. Fetch Data
        if (!$client && !$isAdmin) {
            // Debug message to help you identify if the User isn't a Client
            $this->addFlash('info', "Votre compte n'est pas encore lié à un profil Client. Historique vide.");
        } else {
            // Fetch the dons specific to the client (or all if Admin demo mode)
            $dons = $donRepo->findByClientSearchAndSort($client, $search, $sort, $direction);

            // Fetch AI Recommendation if a client exists
            if ($client) {
                $recommendation = $aiService->getAiRecommendation($client);
            }
        }

        return $this->render('front/don/index.html.twig', [
            'dons' => $dons,
            'currentSearch' => $search,
            'currentSort' => $sort,
            'currentDir' => $direction,
            'aiRecommendation' => $recommendation 
        ]);
    }

    #[Route('/front/don/recommendation', name: 'front_don_recommendation', methods: ['GET'])]
    public function recommendation(
        Request $request,
        ClientRepository $clientRepo,
        DonRepository $donRepo,
        DonationEligibilityService $eligibility
    ): Response {
        $client = $this->currentClient($request, $clientRepo);

        if (!$client) {
            return $this->render('front/don/recommendation.html.twig', [
                'message' => "Aucune recommandation possible : votre compte n'est pas lié à un profil de donneur.",
            ]);
        }

        // Try to find the exact last Don entity
        $lastDon = $donRepo->findLastDonForClient($client);
        
        if ($lastDon) {
            $nextDate = $eligibility->getNextEligibleDate($lastDon);
            return $this->render('front/don/recommendation.html.twig', [
                'client' => $client,
                'lastDon' => $lastDon,
                'nextDate' => $nextDate,
                'source' => 'don_table',
            ]);
        }

        // Fallback: Check the basic 'dernierDon' date on the Client entity
        if ($client->getDernierDon()) {
            $nextDate = $eligibility->getNextEligibleDateFromDate($client->getDernierDon(), 'Sang total');
            return $this->render('front/don/recommendation.html.twig', [
                'client' => $client,
                'nextDate' => $nextDate,
                'source' => 'client.dernierDon',
            ]);
        }

        return $this->render('front/don/recommendation.html.twig', [
            'message' => "Aucun historique trouvé. Vous pouvez faire votre premier don dès aujourd'hui !",
        ]);
    }

    #[Route('/front/don/{id}/delete', name: 'front_don_delete', methods: ['POST'])]
    public function delete(Don $don, Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $client = $this->currentClient($request, $clientRepo);
        $isAdmin = $this->isGranted('ROLE_ADMIN');
        
        // Security Check: Ensure the user owns the donation
        if (!$isAdmin && $don->getClient()?->getId() !== $client?->getId()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer un don qui ne vous appartient pas.');
        }

        if ($this->isCsrfTokenValid('delete_don_' . $don->getId(), (string) $request->request->get('_token'))) {
            $em->remove($don);
            $em->flush();
            $this->addFlash('success', 'Votre don a été supprimé de l\'historique.');
        }

        return $this->redirectToRoute('front_don_index');
    }
}