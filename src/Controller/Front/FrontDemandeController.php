<?php

namespace App\Controller\Front;

use App\Entity\Demande;
use App\Form\DemandeType;
use App\Repository\DemandeRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use OpenAI\Client;
use function curl_init;
use function curl_setopt;
use function curl_exec;
use function curl_close;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/demande')]
final class FrontDemandeController extends AbstractController
{
    #[Route('/', name: 'front_demande_index', methods: ['GET'])]
    public function index(Request $request, DemandeRepository $repo ,CommandeRepository $commandeRepository): Response
    {
        $keyword   = $request->query->get('search');
        $sortField = $request->query->get('sort', 'id');
        $sortDir   = $request->query->get('dir', 'ASC');
        $commandes = $commandeRepository->findAll();
        $user = $this->getUser();

if ($keyword) {
    $demandes = $repo->searchByClient($keyword, $user);
} else {
    $demandes = $repo->sortByClient($sortField, $sortDir, $user);
}


        return $this->render('front/demande/index.html.twig', [
            'demandes'  => $demandes,
            'search'    => $keyword,
            'sortField' => $sortField,
            'sortDir'   => $sortDir,
            'commandes' => $commandes
        ]);
    }

    #[Route('/new', name: 'front_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $demande = new Demande();
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setStatus('EN_ATTENTE');
            $demande->setCreatedAt(new \DateTimeImmutable());
            $demande->setClient($this->getUser());

            $entityManager->persist($demande);
            $entityManager->flush();

            $this->addFlash('success', 'Demande créée avec succès.');
            return $this->redirectToRoute('front_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/demande/new.html.twig', [
            'demande' => $demande,
            'form'    => $form,
        ]);
    }
   #[Route('/analyse-ai-openai', name: 'demande_ai_openai')]
public function analyseOpenAI(DemandeRepository $repo): Response
{
    // 1. Récupérer toutes les demandes
    $demandes = $repo->findAll();

    // 2. AGRÉGATION LOCALE (On prépare les données pour économiser les tokens)
    $historique = [];

foreach ($demandes as $d) {
    $historique[] = [
        "date" => $d->getCreatedAt()->format('Y-m-d'),
        "type" => $d->getTypeSang(),
        "quantite" => $d->getQuantite()
    ];
}

$donneesResumees = json_encode($historique);
    // 3. CONFIGURATION API
    $apiKey = $_ENV['GEMINI2_API_KEY'];
    // Utilisation du modèle Flash 2.0 (le plus rapide et économe)
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent?key=" . $apiKey;


    // Prompt très court pour consommer le moins de quota possible
    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => "Analyse ces demandes de sang historiques et fais une prédiction des futurs besoins. Donne :
1) le groupe le plus demandé
2) le groupe critique bientôt
3) une prédiction pour les prochains jours
Données : " . $donneesResumees]
                ]
            ]
        ]
    ];

    // 4. APPEL API AVEC CURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    // 5. GESTION DES ERREURS DE QUOTA
    if (isset($result['error'])) {
        if ($result['error']['code'] === 429) {
            $analysis = "L'IA est en pause (Quota atteint). Attendez 1 minute avant de rafraîchir la page.";
        } else {
            $analysis = "Erreur API : " . $result['error']['message'];
        }
    } else {
        // Extraction de la réponse texte
        $analysis = $result['candidates'][0]['content']['parts'][0]['text'] ?? "L'IA n'a pas pu générer d'analyse.";
    }

    return $this->render('front/demande/ai_openai.html.twig', [
        'analysis' => $analysis
    ]);
}


    #[Route('/{id}', name: 'front_demande_show', methods: ['GET'])]
    public function show(?Demande $demande): Response
    {
        if (!$demande) {
            $this->addFlash('danger', 'Demande introuvable !');
            return $this->redirectToRoute('front_demande_index');
        }

        return $this->render('front/demande/show.html.twig', [
            'demande' => $demande,
        ]);
    }

    #[Route('/{id}/edit', name: 'front_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $demande->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Demande mise à jour.');
            return $this->redirectToRoute('front_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/demande/edit.html.twig', [
            'demande' => $demande,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: 'front_demande_delete', methods: ['POST'])]
    public function delete(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $demande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($demande);
            $entityManager->flush();
            $this->addFlash('success', 'Demande supprimée.');
        }

        return $this->redirectToRoute('front_demande_index', [], Response::HTTP_SEE_OTHER);
    }
 #[Route('/demandes/pdf', name: 'front_demande_pdf')]
public function pdfGlobal(DemandeRepository $demandeRepository): Response
{
    $demandes = $demandeRepository->findAll();

    $html = $this->renderView('front/demande/pdf_global.html.twig', [
        'demandes' => $demandes,
    ]);

    $options = new Options();
    $options->set('defaultFont', 'Helvetica');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return new Response(
        $dompdf->output(), // ✅ IMPORTANT
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="liste_demandes.pdf"'
        ]
    );
}

#[Route('/front/demande/prediction/pdf', name: 'front_demande_prediction_pdf')]
public function predictionPdf(Request $request): Response
{
    // récupérer analyse envoyée depuis twig
      $analysis = $request->query->get('analysis');

// Sécurité encodage UTF‑8
$analysis = utf8_encode(utf8_decode($analysis));

$html = $this->renderView('front/demande/prediction_pdf.html.twig', [
    'analysis' => $analysis
]);

$options = new Options();
$options->set('defaultFont', 'Helvetica'); // ⚠️ IMPORTANT: change DejaVu Sans

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

return new Response(
    $dompdf->output(),
    200,
    [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachement; filename="prediction.pdf"',
    ]
);
}

   
}