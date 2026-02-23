<?php

namespace App\Controller\Front;

use App\Entity\DossierMed;
use App\Entity\Client;
use App\Form\DossierMedType;
use App\Repository\DossierMedRepository;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

// --- PDF & QR IMPORTS ---
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Encoding\Encoding;

class FrontDossierController extends AbstractController
{
    /**
     * ✅ HELPER: ROBUST CLIENT DETECTION
     */
    private function currentClient(Request $request, ClientRepository $clientRepo): ?Client
    {
        $user = $this->getUser();
        if (!$user) return null;

        // 1. Check Real DB Link
        if (method_exists($user, 'getClient') && $user->getClient()) {
            return $user->getClient();
        }

        // 2. Check Session
        $session = $request->getSession();
        $clientId = $session ? $session->get('client_id') : null;
        if ($clientId) {
            return $clientRepo->find($clientId);
        }

        // 3. Admin Demo Mode
        if ($this->isGranted('ROLE_ADMIN')) {
            return $clientRepo->findOneBy([]); 
        }

        return null;
    }

    #[Route('/front/dossier', name: 'front_dossier_show', methods: ['GET'])]
    public function index(DossierMedRepository $repo, Request $request, ClientRepository $clientRepo): Response
    {
        // 1. Login Check
        if (!$this->getUser()) {
            $this->addFlash('warning', 'Veuillez vous connecter pour accéder à vos dossiers.');
            return $this->redirectToRoute('app_login'); 
        }

        $isMedi = $this->isGranted('ROLE_DOCTOR') || $this->isGranted('ROLE_ADMIN');
        $client = $this->currentClient($request, $clientRepo);

        // 2. Prepare Query
        $qb = $repo->createQueryBuilder('dm')
            ->leftJoin('dm.don', 'don')->addSelect('don')
            ->leftJoin('dm.client', 'c')->addSelect('c')
            ->leftJoin('c.user', 'u')->addSelect('u');

        $dossiers = [];

        if (!$isMedi && !$client) {
            $this->addFlash('info', "Votre compte utilisateur n'est pas encore lié à un profil Client.");
        } 
        else {
            if (!$isMedi && $client) {
                $qb->andWhere('dm.client = :client')->setParameter('client', $client);
            }

            // Search Logic
            $search = $request->query->get('q');
            if ($search) {
                $q = '%' . mb_strtolower($search) . '%';
                $qb->andWhere("
                    LOWER(dm.nom) LIKE :q OR
                    LOWER(dm.prenom) LIKE :q OR
                    LOWER(dm.typeSang) LIKE :q OR
                    LOWER(don.typeDon) LIKE :q
                ")->setParameter('q', $q);
            }

            // Sort Logic
            $sort = $request->query->get('sort', 'date');
            $direction = strtoupper((string)$request->query->get('dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

            $sortMap = [
                'id' => 'dm.id',
                'nom' => 'dm.nom',
                'prenom' => 'dm.prenom',
                'age' => 'dm.age',
                'typeSang' => 'dm.typeSang',
                'date' => 'don.date',
            ];
            $orderBy = $sortMap[$sort] ?? 'don.date';
            $qb->orderBy($orderBy, $direction);

            $dossiers = $qb->getQuery()->getResult();
        }

        // -----------------------------------------------------------
        // 📊 CHART DATA PREPARATION
        // -----------------------------------------------------------
        $chartLabels = [];
        $chartData = [];

        // We reverse the array because charts read Left-to-Right (Oldest -> Newest)
        // usually dossiers are sorted Newest -> Oldest for the table.
        $dossiersForChart = array_reverse($dossiers);

        foreach ($dossiersForChart as $d) {
            // Label: Date
            if ($d->getDon()) {
                $chartLabels[] = $d->getDon()->getDate()->format('d/m/Y');
            } else {
                $chartLabels[] = 'Dossier #' . $d->getId();
            }

            // Data: Weight (Poid)
            $chartData[] = $d->getPoid();
        }

        return $this->render('front/dossier/index.html.twig', [
            'dossiers' => $dossiers,
            'currentSearch' => $request->query->get('q'),
            'currentSort' => $request->query->get('sort', 'date'),
            'currentDir' => $request->query->get('dir', 'DESC'),
            'isMedi' => $isMedi,
            // Pass Data to Twig
            'chartLabels' => json_encode($chartLabels),
            'chartData' => json_encode($chartData),
        ]);
    }

 #[Route('/front/dossier/new', name: 'front_dossier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Sécurité : Réservé aux médecins ou admins
        if (!$this->isGranted('ROLE_DOCTOR') && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('danger', 'Accès réservé au personnel médical.');
            return $this->redirectToRoute('front_dossier_show');
        }

        $dossier = new DossierMed();
        $form = $this->createForm(DossierMedType::class, $dossier);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            
            // 1. Assigner le client automatiquement en fonction du Don sélectionné
            if ($dossier->getDon() && $dossier->getDon()->getClient()) {
                $dossier->setClient($dossier->getDon()->getClient());
            }

            // 2. Vérifier si le formulaire est valide (Maintenant que le CSRF est désactivé, ça passera)
            if ($form->isValid()) {
                $em->persist($dossier);
                $em->flush();
                
                $this->addFlash('success', 'Dossier créé avec succès.');
                return $this->redirectToRoute('front_dossier_show');
            } else {
                // 3. S'il y a ENCORE une erreur (ex: Poids négatif, Taille hors limite), on l'affiche clairement
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('danger', 'Erreur de validation : ' . $error->getMessage());
                }
            }
        }

        return $this->render('front/dossier/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front/dossier/{id}/delete', name: 'front_dossier_delete', methods: ['POST'])]
    public function delete(DossierMed $dossier, Request $request, EntityManagerInterface $em, ClientRepository $clientRepo): Response
    {
        $isMedi = $this->isGranted('ROLE_DOCTOR') || $this->isGranted('ROLE_ADMIN');
        $client = $this->currentClient($request, $clientRepo);

        if (!$isMedi && $dossier->getClient()?->getId() !== $client?->getId()) {
            $this->addFlash('danger', 'Non autorisé.');
            return $this->redirectToRoute('front_dossier_show');
        }

        if ($this->isCsrfTokenValid('delete_dossier_' . $dossier->getId(), (string) $request->request->get('_token'))) {
            $em->remove($dossier);
            $em->flush();
            $this->addFlash('success', 'Dossier supprimé.');
        }

        return $this->redirectToRoute('front_dossier_show');
    }

    #[Route('/front/dossier/{id}/pdf', name: 'front_dossier_pdf', methods: ['GET'])]
    public function generatePdf(DossierMed $dossier, Request $request, ClientRepository $clientRepo): Response
    {
        $isMedi = $this->isGranted('ROLE_DOCTOR') || $this->isGranted('ROLE_ADMIN');
        $client = $this->currentClient($request, $clientRepo);

        if (!$isMedi && $dossier->getClient()?->getId() !== $client?->getId()) {
             $this->addFlash('danger', 'Accès non autorisé.');
             return $this->redirectToRoute('front_dossier_show');
        }

        // 1. OFFLINE DATA GENERATION (RICH TEXT)
        $bmi = 0; $bmiStatus = 'N/A';
        if ($dossier->getTaille() > 0) {
            $bmi = $dossier->getPoid() / (($dossier->getTaille() / 100) ** 2);
            if ($bmi < 18.5) $bmiStatus = 'MAIGRE';
            elseif ($bmi < 25) $bmiStatus = 'NORMAL';
            elseif ($bmi < 30) $bmiStatus = 'SURPOIDS';
            else $bmiStatus = 'OBESE';
        }

        $offlineData = sprintf(
            "📋 [DOSSIER #%d]\n👤 %s %s\n🩸 %s\n📊 Poids: %skg | IMC: %.1f\n🏥 %s",
            $dossier->getId(),
            strtoupper($dossier->getNom()), ucfirst($dossier->getPrenom()),
            $dossier->getTypeSang(),
            $dossier->getPoid(), $bmi,
            $dossier->getDon() ? $dossier->getDon()->getDate()->format('d/m/Y') : 'N/A'
        );
        
        // 2. CREATE QR CODE (SVG)
        $qrCode = new QrCode($offlineData, new Encoding('UTF-8'));
        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $qrCodeBase64 = $result->getDataUri();

        // 3. RENDER PDF
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true); 
        $dompdf = new Dompdf($pdfOptions);

        $html = $this->renderView('front/dossier/pdf.html.twig', [
            'dossier' => $dossier, 'qrCode' => $qrCodeBase64, 'bmi' => $bmi
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="dossier_medical_' . $dossier->getId() . '.pdf"',
        ]);
    }
}