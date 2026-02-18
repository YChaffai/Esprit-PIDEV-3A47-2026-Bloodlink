<?php
namespace App\Controller;

use App\Repository\RendezVousRepository;
use App\Service\AiReportService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Dompdf\Options;

class AiController extends AbstractController
{
    #[Route('/backoffice/ai/report', name: 'app_ai_report')]
public function index(Request $request, RendezVousRepository $rvRepo, AiReportService $aiService)
{
    $dateFilter = $request->query->get('filter_date');
    $criteria = [];
    $contexte = "global";

    if (!empty($dateFilter)) {
        try {
            // On transforme la string en objet DateTime pour ton searchBy
            $dateObj = new \DateTime($dateFilter);
            $criteria['filter_date'] = $dateObj;
            
            // ON UTILISE TON SEARCHBY ICI
            $rvs = $rvRepo->searchBy($criteria);
            
            $contexte = "Analyse précise pour le " . $dateObj->format('d/m/Y');
        } catch (\Exception $e) {
            $rvs = [];
            $contexte = "Erreur technique de lecture de date : " . $dateFilter;
        }
    } else {
        $rvs = $rvRepo->findAll();
    }

    // Préparation des stats pour l'IA
    $groupes = [];
    $totalAge = 0;
    foreach ($rvs as $r) {
        $g = $r->getQuestionnaire()->getGroupSanguin();
        $groupes[$g] = ($groupes[$g] ?? 0) + 1;
        $totalAge += $r->getQuestionnaire()->getAge();
    }

    $statsData = [
        'contexte' => $contexte,
        'total_rendez_vous' => count($rvs),
        'repartition_groupes' => $groupes,
        'moyenne_age' => count($rvs) > 0 ? round($totalAge / count($rvs), 1) : 0
    ];

    if ($request->query->get('ajax') === '1') {
        $report = $aiService->generateReport($statsData, $request->query->get('user_feedback'));
        return new JsonResponse(['report' => $report]);
    }

    return $this->render('ai/report.html.twig', ['stats' => $statsData]);
}

#[Route('/backoffice/ai/export-pdf', name: 'app_ai_export_pdf', methods: ['POST'])]
public function exportPdf(Request $request)
{
    $content = $request->request->get('pdf_content');
    
    // Nettoyage rapide si des symboles traînent encore
    $content = str_replace(['**', '#'], '', $content);

    // Configuration de Dompdf
    $pdfOptions = new Options();
    $pdfOptions->set('defaultFont', 'Helvetica');
    $pdfOptions->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($pdfOptions);
    
    // On crée un HTML propre pour le PDF
    $html = $this->renderView('rendez_vous/pdf_template.html.twig', [
        'content' => $content,
        'date' => new \DateTime()
    ]);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // On envoie le PDF au navigateur
    return new Response($dompdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="Rapport_BloodLink.pdf"'
    ]);
}
}