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
use App\Entity\Questionnaire;
use App\Form\UpdateRendezVousType;
use App\Repository\RendezVousRepository;
use App\Service\SmsService;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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
        

        return $this->redirectToRoute('rendezvous_list', ['client_id' => $client->getId()]);
    }

    $status = $form->isSubmitted() && !$form->isValid() ? 422 : 200;

    return $this->render('rendez_vous/new.html.twig', [
        'form' => $form->createView(),
    ], new Response(null, $status));
}
//   #[Route('/rendez_vous/list/{client_id}', name: 'rendezvous_list')]
//   #[IsGranted('ROLE_CLIENT')]
// public function list(int $client_id, Request $request, RendezVousRepository $rvRepository): Response
// {
//     $form = $this->createForm(RendezVousFilterType::class);
//     $form->handleRequest($request);

//     $queryBuilder = $rvRepository->createQueryBuilder('rv')
//         ->join('rv.questionnaire', 'q')
//         ->where('q.client = :client_id')
//         ->andWhere('rv.status != :status_annule')
//         ->setParameter('client_id', $client_id)
//         ->setParameter('status_annule', 'annulé')
//         ->orderBy('rv.date_don', 'DESC');

//     if ($form->isSubmitted() && $form->isValid()) {
//         $data = $form->getData();

//         if ($data['campagne']) {
//             $queryBuilder->andWhere('q.campagne = :campagne')->setParameter('campagne', $data['campagne']);
//         }

//         if ($data['status']) {
//             $queryBuilder->andWhere('rv.status = :status')->setParameter('status', $data['status']);
//         }
//       // Filtre sur la DATE
//    // Filtre sur la DATE (ex: 2026-02-08)
//     if ($data['filter_date']) {
//         $queryBuilder->andWhere('rv.date_don LIKE :d')
//                      ->setParameter('d', $data['filter_date']->format('Y-m-d') . '%');
//     }

//     // Filtre sur l'HEURE (Format 24h, ex: 14:00)
//     if ($data['filter_time']) {
//         // On utilise LIKE avec des jokers pour isoler l'heure et les minutes dans le DATETIME
//         $queryBuilder->andWhere('rv.date_don LIKE :t')
//                      ->setParameter('t', '%' . $data['filter_time']->format('H:i') . '%');
//     }
//         // if ($data['date']) {
//         //     $dt = $data['date'];
//         //     // Logique identique au Back : filtrage à la minute près
//         //     $start = (clone $dt)->setTime((int)$dt->format('H'), (int)$dt->format('i'), 0);
//         //     $end = (clone $dt)->setTime((int)$dt->format('H'), (int)$dt->format('i'), 59);
            
//         //     $queryBuilder->andWhere('rv.date_don BETWEEN :s AND :e')
//         //                  ->setParameter('s', $start)
//         //                  ->setParameter('e', $end);
//         // }
//          // LOGIQUE DE TRI UNIQUE
//         if (!empty($data['tri_date'])) {
//             $dateForm = $data['tri_date']; // ex : 'date_ASC'
//             $parts = explode('_', $dateForm);
//             $direction = $parts[1]; // 'ASC' ou 'DESC'
//             $queryBuilder->orderBy('rv.date_don', $direction);
//         }
//     }

//     if ($request->isXmlHttpRequest()) {
//         return $this->render('rendez_vous/_list_content.html.twig', [
//             'rendezvous' => $queryBuilder->getQuery()->getResult(),
//              'client_id' => $client_id
//         ]);
//     }

//     return $this->render('rendez_vous/list.html.twig', [
//         'rendezvous' => $queryBuilder->getQuery()->getResult(),
//         'filterForm' => $form->createView(),
//         'client_id' => $client_id
//     ]);
// }

#[Route('/rendez_vous/list/{client_id}', name: 'rendezvous_list')]
public function list(int $client_id, Request $request, RendezVousRepository $repo): Response
{
    $form = $this->createForm(RendezVousFilterType::class);
    $form->handleRequest($request);

    $criteria = $form->isSubmitted() && $form->isValid() ? $form->getData() : [];
    $criteria['client_id'] = $client_id;

    // Vous devez créer cette méthode searchBy dans votre RendezVousRepository
    $rendezvous = $repo->searchBy($criteria);

    // --- LOGIQUE DYNAMIQUE ---
    if ($request->isXmlHttpRequest()) {
        return $this->render('rendez_vous/_list_content.html.twig', [
            'rendezvous' => $rendezvous,
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

        return $this->render('rendez_vous/update.html.twig', [
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
public function listback(Request $request, RendezVousRepository $rendezVousRepository): Response
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

    if ($request->isXmlHttpRequest()) {
        return $this->render('rendez_vous/_listback_table.html.twig', [
            'rendezvous' => $rendezvous,
        ]);
    }

    return $this->render('rendez_vous/listback.html.twig', [
        'rendezvous' => $rendezvous,
        'filterForm' => $form->createView(),
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

   
}
