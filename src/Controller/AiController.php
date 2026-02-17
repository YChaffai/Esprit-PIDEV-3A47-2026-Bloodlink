<?php
namespace App\Controller;
use App\Repository\RendezVousRepository;
use App\Repository\QuestionnaireRepository;
use App\Service\AiReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AiController extends AbstractController
{// src/Controller/AiController.php

#[Route('/backoffice/ai/report', name: 'app_ai_report')]
public function index(Request $request, QuestionnaireRepository $qRepo, RendezVousRepository $rvRepo, AiReportService $aiService)
{
    $qs = $qRepo->findAll();
    $rvs = $rvRepo->findAll();
    $totalQ = count($qs);

    // 1. Calcul des statistiques
    $statsData = [
        'donneurs' => [
            'total' => $totalQ,
            'age_moyen' => $totalQ > 0 ? round(array_sum(array_map(fn($q) => $q->getAge(), $qs)) / $totalQ, 1) : 0,
            'poids_moyen' => $totalQ > 0 ? round(array_sum(array_map(fn($q) => $q->getPoids(), $qs)) / $totalQ, 1) : 0,
            'repartition_groupes' => array_count_values(array_map(fn($q) => $q->getGroupSanguin(), $qs)),
        ],
        'logistique' => [
            'total_rdv' => count($rvs),
            'etats_rdv' => array_count_values(array_map(fn($r) => $r->getStatus(), $rvs)),
        ],
        'derniere_activite' => $totalQ > 0 ? end($qs)->getDate()->format('d/m/Y') : 'N/A'
    ];

    $report = null;

    // 2. On déclenche l'IA uniquement si le bouton est cliqué
    if ($request->query->get('analyze') === '1') {
        $statsData['json_stats'] = json_encode($statsData);
        $report = $aiService->generateReport($statsData);
    }

    return $this->render('ai/report.html.twig', [
        'report' => $report,
        'stats' => $statsData
    ]);
}
}