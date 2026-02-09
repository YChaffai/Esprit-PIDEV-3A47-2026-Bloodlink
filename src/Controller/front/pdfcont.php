<?php

namespace App\Controller\front;

use App\Repository\DemandeRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class pdfcont extends AbstractController
{
    #[Route('/demande/export/pdf', name: 'front_demande_pdf')]
    public function pdf(DemandeRepository $demandeRepository): Response
    {
        $demandes = $demandeRepository->findAll();

        // Générer le HTML depuis Twig
        $html = $this->renderView('front/pdfdemande.html.twig', [
            'demandes' => $demandes
        ]);

        // Configurer Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true); // pour charger CSS/images
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Retourner le PDF en téléchargement
        $pdfOutput = $dompdf->output();

        return new Response($pdfOutput, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="demandes.pdf"',
            'Content-Length' => strlen($pdfOutput)
        ]);
    }
}
