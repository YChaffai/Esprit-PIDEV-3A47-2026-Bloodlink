<?php

namespace App\Controller;
use App\Form\RendezVousType;
use App\Form\RendezVousFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CampagneRepository;
use App\Repository\ClientRepository;
use App\Entity\RendezVous;
use App\Form\UpdateRendezVousType;
use App\Repository\RendezVousRepository;
use App\Service\SmsService;
use App\Service\AiReportService;
use App\Service\PdfGeneratorService;
use App\Service\GoogleCalendarService;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class RendezVousController extends AbstractController
{
    #[Route('/rendez/vous', name: 'app_rendez_vous')]
    public function index(): Response
    {
        return $this->render('rendez_vous/index.html.twig', [
            'controller_name' => 'RendezVousController',
        ]);
    }

     //-------------------------------------------frontoffice--------------------------------------------------------------//
    #[Route('/rendez_vous/new', name: 'rendezvous_new')]
    #[IsGranted('ROLE_CLIENT')]
public function new(Request $request, EntityManagerInterface $em, CampagneRepository $campagneRepo, ClientRepository $clientRepo,
        SmsService $smsService): Response
{
     $questionnaire = $request->getSession()->get('pending_questionnaire');
    
    // if (!$questionnaire) {
    //     throw $this->createNotFoundException('Questionnaire non trouvé');
    // }

    $client = $questionnaire->getClient();
    
    // --- ÉTAPE CRUCIALE : Dénormalisation ---
    // On enregistre les infos du client DIRECTEMENT dans le questionnaire 
    // pour faciliter les futurs filtres dans le backoffice.
    // $questionnaire->setNom($client->getNom());
    // $questionnaire->setPrenom($client->getPrenom());
    // ----------------------------------------
    $campagneManaged = $campagneRepo->find($questionnaire->getCampagne()->getId());
    $clientManaged = $clientRepo->find($questionnaire->getClient()->getId());

    $questionnaire->setCampagne($campagneManaged);
    $questionnaire->setClient($clientManaged);
    $rendezVous = new RendezVous();
    $rendezVous->setQuestionnaire($questionnaire);
    $rendezVous->setStatus("en attente");

    $form = $this->createForm(RendezVousType::class, $rendezVous);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($questionnaire); 
        $em->flush();
        $em->persist($rendezVous);
         $em->flush();

          // --- 3. APPEL DU SERVICE SMS ---
            // On vérifie si le client a un numéro de téléphone
            $telephone = $clientManaged->getUser()->getTelephone();
            
            if ($telephone) {
                // On formate la date pour qu'elle soit lisible dans le SMS
                $dateLisible = $rendezVous->getDateDon()->format('d/m/Y à H:i');
                $nomLieu = $rendezVous->getEntite()->getNom();

                $smsService->sendRendezVousConfirmation(
                    $telephone,                  // Numéro du client
                    $clientManaged->getPrenom(), // Prénom
                    $dateLisible,                // Date
                    $nomLieu                     // Lieu
                );
                
                $this->addFlash('success', 'Rendez-vous confirmé et SMS envoyé !');
            } else {
                $this->addFlash('success', 'Rendez-vous confirmé (Aucun SMS envoyé : numéro manquant).');
            }
            // ------------
          // --- 4. Ajouter l'événement à Google Calendar ---
        // Créez l'événement sur Google Calendar
       $request->getSession()->set('pending_rdv_data', [
        'date_don' => $rendezVous->getDateDon(),
        'campagne_id' => $campagneManaged->getId(),
        'client_id' => $clientManaged->getId(),
        'entite_id' => $rendezVous->getEntite()->getNom(),
        // Ajoute ici d'autres IDs si nécessaire (ex: questionnaire_id)
    ]);

        // Ajouter un lien vers l'événement Google Calendar
        $this->addFlash('success', 'Rendez-vous ajouté à Google Calendar !');

// // On redirige vers Google
//     return $this->redirectToRoute('connect_google_start');
        return $this->redirectToRoute('rendezvous_list', ['client_id' => $client->getId(), 'showGoogleModal' => 1]);
    }

    $status = $form->isSubmitted() && !$form->isValid() ? 422 : 200;

    return $this->render('rendez_vous/new.html.twig', [
        'form' => $form->createView(),
    ], new Response(null, $status));
}

#[Route('/rendez_vous/list/{client_id}', name: 'rendezvous_list')]
public function list(int $client_id, Request $request, RendezVousRepository $rvRepository): Response
{
    $form = $this->createForm(RendezVousFilterType::class);
    $form->handleRequest($request);

    $criteria = ($form->isSubmitted() && $form->isValid()) ? $form->getData() : [];
    
    // On appelle la méthode ISOLÉE
    $rendezvous = $rvRepository->searchByClient($client_id, $criteria);

    if ($request->isXmlHttpRequest()) {
        return $this->render('rendez_vous/_list_content.html.twig', [
            'rendezvous' => $rendezvous,
            'client_id' => $client_id
        ]);
    }

    return $this->render('rendez_vous/list.html.twig', [
        'rendezvous' => $rendezvous,
        'filterForm' => $form->createView(),
        'client_id' => $client_id
    ]);
}

     #[Route('/rendez_vous/update/{id}', name:'rendezvous_update')]
     #[IsGranted('ROLE_CLIENT')]
    public function update(int $id, Request $request, RendezVousRepository $rendezVousRepository, EntityManagerInterface $em){
        $rendezvous = $rendezVousRepository->find($id);
        $clientId = $rendezvous->getQuestionnaire()->getClient()->getId();

        $form= $this->createForm(UpdateRendezVousType::class, $rendezvous);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $em->flush();
            return $this->redirectToRoute('rendezvous_list',  ['client_id' => $clientId]);
        }
            $status = $form->isSubmitted() && !$form->isValid() ? 422 : 200;

        return $this->render('rendez_vous/updatefront.html.twig', [
            "form" => $form
        ], new Response(null, $status));
    }
    
    #[Route('/rendez_vous/delete/{id}', name:'rendezvous_delete')]
    #[IsGranted('ROLE_CLIENT')]
    public function delete($id, EntityManagerInterface $em, RendezVousRepository $rendezVousRepository){
        $rendezvous = $rendezVousRepository->find($id);
        $clientId = $rendezvous->getQuestionnaire()->getClient()->getId();
        $questionnaire = $rendezvous->getQuestionnaire();
        // $em->remove($questionnaire);
        // $em->flush();
        $rendezvous->setStatus('annulé');
        $em->flush();
        
        return $this->redirectToRoute('rendezvous_list',  ['client_id' => $clientId]);

    }

    //-------------------------------------------backoffice--------------------------------------------------------------//
 #[Route('backoffice/rendez_vous/new', name: 'rendezvousback_new')]
 #[IsGranted('ROLE_ADMIN')]
public function newback(Request $request, EntityManagerInterface $em, CampagneRepository $campagneRepo, ClientRepository $clientRepo,
        SmsService $smsService): Response
{
     $questionnaire = $request->getSession()->get('pending_questionnaireback');
    
    // if (!$questionnaire) {
    //     throw $this->createNotFoundException('Questionnaire non trouvé');
    // }

    $client = $questionnaire->getClient();
    
    // --- ÉTAPE CRUCIALE : Dénormalisation ---
    // On enregistre les infos du client DIRECTEMENT dans le questionnaire 
    // pour faciliter les futurs filtres dans le backoffice.
    // $questionnaire->setNom($client->getNom());
    // $questionnaire->setPrenom($client->getPrenom());
    // ----------------------------------------
    $campagneManaged = $campagneRepo->find($questionnaire->getCampagne()->getId());
    $clientManaged = $clientRepo->find($questionnaire->getClient()->getId());

    $questionnaire->setCampagne($campagneManaged);
    $questionnaire->setClient($clientManaged);
    $rendezVous = new RendezVous();
    $rendezVous->setQuestionnaire($questionnaire);
    $rendezVous->setStatus("en attente");

    $form = $this->createForm(RendezVousType::class, $rendezVous);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($questionnaire); 
        $em->persist($rendezVous);
         $em->flush();

          // --- 3. APPEL DU SERVICE SMS ---
            // On vérifie si le client a un numéro de téléphone
            $telephone = $clientManaged->getUser()->getTelephone();
            
            if ($telephone) {
                // On formate la date pour qu'elle soit lisible dans le SMS
                $dateLisible = $rendezVous->getDateDon()->format('d/m/Y à H:i');
                $nomLieu = $rendezVous->getEntite()->getNom();

                $smsService->sendRendezVousConfirmation(
                    $telephone,                  // Numéro du client
                    $clientManaged->getPrenom(), // Prénom
                    $dateLisible,                // Date
                    $nomLieu                     // Lieu
                );
                
                $this->addFlash('success', 'Rendez-vous confirmé et SMS envoyé !');
            } else {
                $this->addFlash('success', 'Rendez-vous confirmé (Aucun SMS envoyé : numéro manquant).');
            }
            // ------------
        
$request->getSession()->remove('pending_questionnaireback');
        return $this->redirectToRoute('rendezvousback_list');
    }

    $status = $form->isSubmitted() && !$form->isValid() ? 422 : 200;

    return $this->render('rendez_vous/newback.html.twig', [
        'form' => $form->createView(),
    ], new Response(null, $status));
}


#[Route('/backoffice/rendezvous', name: 'rendezvousback_list')]
#[IsGranted('ROLE_ADMIN')]
public function listback(Request $request, RendezVousRepository $rendezVousRepository, AiReportService $aiService): Response
{
    $form = $this->createForm(RendezVousFilterType::class);
    $form->handleRequest($request);

    $criteria = [];
    if ($form->isSubmitted() && $form->isValid()) {
        $criteria = $form->getData();
    }

    // Add unified search parameter from request
    $criteria['search'] = $request->query->get('search');

    $rendezvous = $rendezVousRepository->searchBy($criteria);

    // // --- PARTIE IA ---
    // $report = null;
    // $userFeedback = $request->query->get('user_feedback'); // On récupère la demande de modif

    // if ($request->query->get('analyze') === '1') {
    //     // On prépare les stats basées sur les résultats filtrés ($rendezvous)
    //     $statsData = [
    //         'total_rdv' => count($rendezvous),
    //         'user_instruction' => $userFeedback, // On passe l'instruction de l'utilisateur
    //         'json_stats' => json_encode(array_map(fn($r) => [
    //             'date' => $r->getDateDon()->format('d/m/Y'),
    //             'status' => $r->getStatus(),
    //             'type' => $r->getQuestionnaire()->getCampagne()->getTypeSang()
    //         ], $rendezvous))
    //     ];
    //     $report = $aiService->generateReport($statsData);
      
    // }

    if ($request->isXmlHttpRequest()) {
        return $this->render('rendez_vous/_listback_table.html.twig', [
            'rendezvous' => $rendezvous,
        ]);
    }

    return $this->render('rendez_vous/listback.html.twig', [
        'rendezvous' => $rendezvous,
        'filterForm' => $form->createView()
        // 'report' => $report,
        // 'last_feedback' => $userFeedback,
        // 'fileUrl' => $fileUrl ?? '' // Pass the file URL for the frontend to use

    ]);
}

    #[Route('/backoffice/rendez_vous/update/{id}', name:'rendezvousback_update')]
    #[IsGranted('ROLE_ADMIN')]
    public function updateback(int $id, Request $request, RendezVousRepository $rendezVousRepository, EntityManagerInterface $em){
        $rendezvous = $rendezVousRepository->find($id);
        $form= $this->createForm(UpdateRendezVousType::class, $rendezvous);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $em->flush();
            return $this->redirectToRoute('rendezvousback_list');
        }
        return $this->render('rendez_vous/update.html.twig', [
            "form" => $form
        ]);
    }

    #[Route('/backoffice/rendez_vous/delete/{id}', name:'rendezvousback_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteback($id, EntityManagerInterface $em, RendezVousRepository $rendezVousRepository){
        $rendezvous = $rendezVousRepository->find($id);
        $em->remove($rendezvous);
        $em->flush();
        return $this->redirectToRoute('rendezvousback_list');

    }

   

//excel export//
#[Route('/backoffice/rendez_vous/export_rendezvous', name:'export_rendezvous')]
public function exportRendezvous(Request $request, RendezVousRepository $repo): StreamedResponse
{
    // On récupère les filtres directement (pas besoin de handleRequest ici si on veut rester simple)
    // On nettoie pour ne garder que les clés dont searchBy a besoin
    $criteria = $request->query->all();
    
    // Si tes filtres sont dans un tableau 'rendez_vous_filter', on l'extrait
    if (isset($criteria['rendez_vous_filter'])) {
        $criteria = $criteria['rendez_vous_filter'];
    }

    // On fait cela ici pour que le Repository reçoive bien un OBJET et puisse faire ->format() khaterhom string tawa
    if (!empty($criteria['filter_date']) && is_string($criteria['filter_date'])) {
        $criteria['filter_date'] = new \DateTime($criteria['filter_date']);
    }
    
    if (!empty($criteria['filter_time']) && is_string($criteria['filter_time'])) {
        // Pour l'heure, on s'assure que PHP interprète bien le format H:i
        $criteria['filter_time'] = new \DateTime($criteria['filter_time']);
    }
    // On récupère les résultats filtrés
    $rendezvousList = $repo->searchBy($criteria);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // --- Design Titre ---
    $sheet->setCellValue('A1', 'LISTE DES RENDEZ-VOUS');
    $sheet->mergeCells('A1:E1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
    $sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CC0000');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

    // --- En-têtes ---
    $headers = ['ID', 'Donneur', 'Date & Lieu', 'Campagne', 'Statut'];
    $sheet->fromArray([$headers], null, 'A2');
    $sheet->getStyle('A2:E2')->getFont()->setBold(true);

    // --- Données ---
    $rows = [];
    foreach ($rendezvousList as $rv) {
        $q = $rv->getQuestionnaire();
        $rows[] = [
            $rv->getId(),
            $q->getPrenom() . ' ' . $q->getNom(),
            $rv->getDateDon()->format('d/m/Y H:i') . ' - ' . $rv->getEntite()->getNom(),
            $q->getCampagne()->getTitre(),
            $rv->getStatus()
        ];
    }
    $sheet->fromArray($rows, null, 'A3');

    foreach (range('A', 'E') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    return new StreamedResponse(function() use ($writer) {
        $writer->save('php://output');
    }, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="export_rendezvous.xlsx"',
    ]);
}



#[Route('/backoffice/rendezvous/report/download', name: 'rendezvous_report_download')]
public function downloadReport(): Response
{
    // Define the PDF file path. You may need to adjust it based on where you saved the PDF.
    $pdfFilePath = '/path/to/generated/file/rapport.pdf';

    // Check if the file exists
    if (!file_exists($pdfFilePath)) {
        throw $this->createNotFoundException('File not found');
    }

    // Serve the file as a response
    return new Response(
        file_get_contents($pdfFilePath),
        200,
        [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rapport.pdf"'
        ]
    );
}


}
