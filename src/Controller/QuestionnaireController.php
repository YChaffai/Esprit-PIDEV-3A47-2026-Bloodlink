<?php

namespace App\Controller;

use App\Form\QuestionnaireType;
use App\Form\UpdateQuestionnaireType;

use App\Form\QuestionnaireFilterType;
use App\Form\CreateQuestionnaireBackType;
use App\Entity\Questionnaire;
use App\Repository\QuestionnaireRepository;
use App\Repository\ClientRepository;
use App\Repository\CompagneRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class QuestionnaireController extends AbstractController
{
  //-------------------------------------------frontoffice--------------------------------------------------------------//
  #[Route('/questionnaire/new/{id}/{client_id}',  name: 'questionnaire_new')]
  #[IsGranted('ROLE_CLIENT')]
  public function new(int $id, int $client_id, Request $request, CompagneRepository $campagneRepo,  ClientRepository $clientRepository, EntityManagerInterface $em, QuestionnaireRepository $questionnaireRepo)
  {
    $campagne = $campagneRepo->find($id);
$client = $clientRepository->findOneBy(['user' => $client_id]);
    // TEST DE DÉBOGAGE
if (!$client) {
    throw new \Exception("Le client avec l'ID " . $client_id . " n'existe pas dans la table Client !");
}
    $existing = $questionnaireRepo->findOneBy([
      'campagne' => $campagne,
      'client' => $client
    ]);

    if ($existing) {
      // Ajoute un message flash pour informer l'utilisateur
      $this->addFlash('danger', 'Désolé, vous avez déjà rempli un questionnaire pour cette campagne.');

      // Redirige vers la liste des campagnes ou une autre page de ton choix
      return $this->redirectToRoute('campagne_list');
    }

    $questionnaire = new Questionnaire();
    $questionnaire->setCampagne($campagne);
    $questionnaire->setNom($client->getNom());
    $questionnaire->setPrenom($client->getPrenom());

    $questionnaire->setGroupSanguin($client->getTypeSang());
    $form = $this->createForm(QuestionnaireType::class, $questionnaire);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      // dd($form->getErrors(true));
      $questionnaire->setClient($client);
      $questionnaire->setDate(date: new DateTime('now', new \DateTimeZone('Africa/Tunis')));
      $em->persist($questionnaire);
      // $em->flush();
      $request->getSession()->set('pending_questionnaire', $questionnaire);
      return $this->redirectToRoute('rendezvous_new');
    }
    $status = $form->isSubmitted() && !$form->isValid() ? 422 : 200;


    return $this->render('questionnaire/new.html.twig', [
      'form' => $form->createView(),
      'campagne' => $campagne,
      'client_id' => $client,

    ], new Response(null, $status));
  }

#[Route('/questionnaire/list/{client_id}', name: 'questionnaire_list')]
#[IsGranted('ROLE_CLIENT')]
public function list(Request $request, int $client_id, QuestionnaireRepository $repo): Response
{
    $form = $this->createForm(QuestionnaireFilterType::class);
    $form->handleRequest($request);

    // On récupère les données si soumis, sinon tableau vide
    $criteria = ($form->isSubmitted() && $form->isValid()) ? $form->getData() : [];

    // Appel à la méthode optimisée du Repository
    $questionnaires = $repo->searchByClient($client_id, $criteria);

    // Gestion AJAX
    if ($request->isXmlHttpRequest()) {
        return $this->render('questionnaire/_list_content.html.twig', [
            'questionnaires' => $questionnaires,
            'client_id' => $client_id
        ]);
    }

    return $this->render('questionnaire/list.html.twig', [
        'questionnaires' => $questionnaires,
        'filterForm' => $form->createView(),
        'client_id' => $client_id
    ]);
}
  #[Route('/questionnaire/update/{id}', name: 'questionnaire_update')]
  #[IsGranted('ROLE_CLIENT')]
  public function update($id, Request $request, QuestionnaireRepository $questionnaireRepository, EntityManagerInterface $em)
  {
    $questionnaire = $questionnaireRepository->find($id);
    $clientId = $questionnaire->getClient()->getId();
    $form = $this->createForm(UpdateQuestionnaireType::class, $questionnaire);
    $form->handleRequest($request);
    if ($form->isSubmitted()) {
      $questionnaire->setDate(date: new DateTime('now', new \DateTimeZone('Africa/Tunis')));
      $em->flush();
      return $this->redirectToRoute('questionnaire_list', ['client_id' => $clientId]);
    }
    return $this->render('questionnaire/updatefront.html.twig', [
      "form" => $form,
      'questionnaires' => $questionnaire
    ]);
  }

  #[Route('/questionnaire/delete/{id}', name: 'questionnaire_delete')]
  #[IsGranted('ROLE_CLIENT')]
  public function delete($id, EntityManagerInterface $em, QuestionnaireRepository $questionnaireRepository)
  {
    $questionnaire = $questionnaireRepository->find($id);
    $clientId = $questionnaire->getClient()->getId();
    $em->remove($questionnaire);
    $em->flush();
    return $this->redirectToRoute('questionnaire_list',  ['client_id' => $clientId]);
  }

  #[Route('/questionnaire/details/{id}', name: 'questionnaire_details')]
  #[IsGranted('ROLE_CLIENT')]
  public function details($id, QuestionnaireRepository $questionnaireRepository): Response
  {
    $questionnaire = $questionnaireRepository->find($id);
    return $this->render('questionnaire/details.html.twig', [
      "questionnaires" => $questionnaire,
    ]);
  }

  //-------------------------------------------backoffice--------------------------------------------------------------//

  #[Route('/user/questionnaire/details/{id}', name: 'questionnaireback_details')]
  #[IsGranted('ROLE_ADMIN')]
  public function detailsback($id, QuestionnaireRepository $questionnaireRepository): Response
  {
    $questionnaire = $questionnaireRepository->find($id);

    return $this->render('questionnaire/detailsback.html.twig', [
      "questionnaires" => $questionnaire,
    ]);
  }

  #[Route('/user/questionnaire/detailsrv/{id}', name: 'questionnairebackrv_details')]
  #[IsGranted('ROLE_ADMIN')]
  public function detailsbackrv($id, QuestionnaireRepository $questionnaireRepository): Response
  {
    $questionnaire = $questionnaireRepository->find($id);

    return $this->render('questionnaire/detailsbackrv.html.twig', [
      "questionnaires" => $questionnaire,
    ]);
  }
  #[Route('/user/questionnaires', name: 'questionnaireback_list')]
  #[IsGranted('ROLE_ADMIN')]
    public function listback(Request $request, QuestionnaireRepository $questionnaireRepository): Response
    {
        // 1. On crée le formulaire de filtre
        $form = $this->createForm(QuestionnaireFilterType::class);
        $form->handleRequest($request);

        // 2. On prépare les critères
        $criteria = [];
        if ($form->isSubmitted() && $form->isValid()) {
            $criteria = $form->getData();
        }

        // Add unified search parameter from request
        $criteria['search'] = $request->query->get('search');

        // 3. On appelle le repository
        $questionnaires = $questionnaireRepository->searchBy($criteria);

        // 4. On envoie 'filterForm' à la vue listback.html.twig
        if ($request->isXmlHttpRequest()) {
            return $this->render('questionnaire/_listback_table.html.twig', [
                'questionnaires' => $questionnaires,
            ]);
        }

        return $this->render('questionnaire/listback.html.twig', [
            'questionnaires' => $questionnaires,
            'filterForm' => $form->createView(),
        ]);
    }

  #[Route('/user/questionnaire/new', name: 'questionnaireback_new')]
  #[IsGranted('ROLE_ADMIN')]
  public function newback(Request $request, ClientRepository $clientRepository, EntityManagerInterface $em): Response
  {
    $questionnaire = new Questionnaire();

    $form = $this->createForm(CreateQuestionnaireBackType::class, $questionnaire);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      $campagne = $questionnaire->getCampagne();
      $clientEmail = $form->get('client')->getData(); // L'email du client saisi
      $client = $clientRepository->findOneByEmail($clientEmail); // Trouver le client par email via User

      if ($client) {
        // Associer la campagne et le client au questionnaire
        $questionnaire->setClient($client);
        $questionnaire->setNom($client->getUser()->getNom());
        $questionnaire->setPrenom($client->getUser()->getPrenom());
        $questionnaire->setDate(new DateTime('now', new \DateTimeZone('Africa/Tunis')));
        $questionnaire->setGroupSanguin($client->getTypeSang());

        // Persister et enregistrer le questionnaire
        $em->persist($questionnaire);
        // $em->flush();
        // 3. On utilise la clé attendue par le controller de destination
        $request->getSession()->set('pending_questionnaireback', $questionnaire);
        // Rediriger vers la page du rendez-vous (en passant l'ID du questionnaire)
        return $this->redirectToRoute('rendezvousback_new', ['questionnaire_id' => $questionnaire->getId()]);
      } else {
        // Si le client n'est pas trouvé, ajouter un message d'erreur
        $this->addFlash('error', 'Client non trouvé avec cet email.');
      }
    }
    $status = $form->isSubmitted() && !$form->isValid() ? 422 : 200;

    return $this->render('questionnaire/newback.html.twig', [
      'form' => $form->createView(),
    ], new Response(null, $status));
  }

 #[Route('/backoffice/questionnaire/export', name: 'export_questionnaires')]
public function exportQuestionnaires(Request $request, QuestionnaireRepository $repo): StreamedResponse
{
    // 1. Récupération et nettoyage des filtres (comme pour les RDV)
    $criteria = $request->query->all();
   // Extraction si les filtres sont encapsulés dans le nom du formulaire
    if (isset($criteria['questionnaire_filter'])) {
        $criteria = $criteria['questionnaire_filter'];
    }

    // 3. LA CORRECTION : On transforme les chaînes de caractères en Objets DateTime
    // On fait cela ici pour que le Repository reçoive bien un OBJET et puisse faire ->format()
    if (!empty($criteria['filter_date']) && is_string($criteria['filter_date'])) {
        $criteria['filter_date'] = new \DateTime($criteria['filter_date']);
    }
    
    if (!empty($criteria['filter_time']) && is_string($criteria['filter_time'])) {
        // Pour l'heure, on s'assure que PHP interprète bien le format H:i
        $criteria['filter_time'] = new \DateTime($criteria['filter_time']);
    }
    // 2. Récupération des données filtrées
    $list = $repo->searchBy($criteria); // Assure-toi que ton Repo Questionnaire a aussi un searchBy

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // --- Design Titre ---
    $sheet->setCellValue('A1', 'LISTE DES QUESTIONNAIRES');
    $sheet->mergeCells('A1:H1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
    $sheet->getStyle('A1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('212529'); // Noir Premium
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

    // --- En-têtes (Basé sur ton tableau Twig) ---
    $headers = ['ID', 'Donneur', 'Sexe', 'Age', 'Poids', 'Groupe Sanguin', 'Campagne', 'Date de soumission'];
    $sheet->fromArray([$headers], null, 'A2');
    $sheet->getStyle('A2:H2')->getFont()->setBold(true);

    // --- Données ---
    $rows = [];
    foreach ($list as $q) {
        $rows[] = [
            $q->getId(),
            strtoupper($q->getNom()) . ' ' . $q->getPrenom(),
            $q->getSexe(), 
            $q->getAge() . ' ans', 
            $q->getPoids() . ' kg',
            $q->getGroupSanguin(),
            $q->getCampagne()->getTitre(),
            $q->getDate()->format('d/m/Y H:i')
        ];
    }
    $sheet->fromArray($rows, null, 'A3');

    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    $writer = new Xlsx($spreadsheet);
    return new StreamedResponse(function() use ($writer) {
        $writer->save('php://output');
    }, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="export_questionnaires.xlsx"',
    ]);
}

}
