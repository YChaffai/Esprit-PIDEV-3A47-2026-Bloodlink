<?php

namespace App\Controller\Back;

use App\Entity\DossierMed;
use App\Form\DossierMedType;
use App\Repository\DossierMedRepository;
use App\Repository\DonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\DonorPredictionService;
use App\Service\DonorRetentionAIService;

#[Route('/back/dossier')]
class DossierBackController extends AbstractController
{
    #[Route('/', name: 'back_dossier_index', methods: ['GET'])]
    public function index(DossierMedRepository $dossierMedRepository, DonRepository $donRepo): Response
    {
        // 1. On récupère tous les dossiers, du plus récent au plus ancien
        $tousLesDossiers = $dossierMedRepository->findBy([], ['id' => 'DESC']);
        
        $dossiersUniques = [];
        $clientsVus = [];
        $nombreDonsParClient = []; 

        // 2. On filtre pour ne garder qu'un seul dossier par patient
        foreach ($tousLesDossiers as $dossier) {
            $client = $dossier->getClient();
            // Si le dossier n'a pas de client (sécurité), on utilise son propre ID
            $clientId = $client ? $client->getId() : 'sans_client_' . $dossier->getId();
            
            // Si on n'a pas encore vu ce patient dans la liste...
            if (!in_array($clientId, $clientsVus)) {
                $dossiersUniques[] = $dossier; // On garde son dossier le plus récent
                $clientsVus[] = $clientId;     // On le marque comme "vu"
                
                // On compte son nombre total de dons
                if ($client) {
                    $nombreDonsParClient[$client->getId()] = $donRepo->count(['client' => $client]);
                }
            }
        }

        return $this->render('back/dossier/index.html.twig', [
            // On envoie la liste filtrée (1 patient = 1 ligne)
            'dossiers' => $dossiersUniques, 
            'nombreDons' => $nombreDonsParClient,
        ]);
    }

    #[Route('/new', name: 'back_dossier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, DonRepository $donRepo): Response
    {
        $dossierMed = new DossierMed();
        
        // Pré-remplissage si on vient d'un Don spécifique
        $donId = $request->query->get('don_id');
        if ($donId) {
            $don = $donRepo->find($donId);
            if ($don) {
                $dossierMed->setDon($don);
                $dossierMed->setClient($don->getClient());
            }
        }

        $form = $this->createForm(DossierMedType::class, $dossierMed);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Logique d'assignation automatique avant validation
            if ($dossierMed->getDon() && $dossierMed->getDon()->getClient()) {
                $dossierMed->setClient($dossierMed->getDon()->getClient());
            }

            if ($form->isValid()) {
                $entityManager->persist($dossierMed);
                $entityManager->flush();

                $this->addFlash('success', 'Dossier créé avec succès.');
                return $this->redirectToRoute('back_dossier_index');
            } else {
                // 🔥 DÉBOGAGE : Affiche l'erreur exacte pour chaque champ
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('danger', 'Erreur : ' . $error->getMessage());
                }
            }
        }

        return $this->render('back/dossier/new.html.twig', [
            'dossier' => $dossierMed,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'back_dossier_show', methods: ['GET'])]
    public function show(DossierMed $dossierMed, DonRepository $donRepo, DonorRetentionAIService $aiService): Response
    {
        $historiqueDons = [];
        $nombreDons = 0;
        
        if ($dossierMed->getClient()) {
            $historiqueDons = $donRepo->findBy(
                ['client' => $dossierMed->getClient()], 
                ['date' => 'DESC']
            );
            $nombreDons = count($historiqueDons);
        }

        // 🔥 Appel de notre IA Rubix ML
        $aiAnalysis = $aiService->predictRetention($dossierMed, $nombreDons);

        return $this->render('back/dossier/show.html.twig', [
            'dossier' => $dossierMed,
            'historiqueDons' => $historiqueDons,
            'aiAnalysis' => $aiAnalysis, // On envoie l'analyse à Twig
        ]);
    }

    #[Route('/{id}/edit', name: 'back_dossier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DossierMed $dossierMed, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DossierMedType::class, $dossierMed);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // C'est cette ligne qui sauvegarde les modifications en base de données !
            $entityManager->flush();

            $this->addFlash('success', 'Dossier modifié avec succès.');
            return $this->redirectToRoute('back_dossier_index'); // ou back_dossier_show
        }

        return $this->render('back/dossier/edit.html.twig', [
            'dossier' => $dossierMed,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'back_dossier_delete', methods: ['POST'])]
    public function delete(Request $request, DossierMed $dossierMed, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$dossierMed->getId(), (string) $request->request->get('_token'))) {
            $entityManager->remove($dossierMed);
            $entityManager->flush();
            $this->addFlash('success', 'Dossier supprimé.');
        }

        return $this->redirectToRoute('back_dossier_index');
    }
}