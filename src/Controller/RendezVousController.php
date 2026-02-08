<?php

namespace App\Controller;
use App\Form\RendezVousType;
use App\Form\RendezVousFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\QuestionnaireRepository;
use App\Repository\ClientRepository;
use App\Entity\RendezVous;
use App\Form\UpdateRendezVousType;
use App\Repository\RendezVousRepository;

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
    #[Route('/rendez_vous/new/{questionnaire_id}', name: 'rendezvous_new')]
public function new(int $questionnaire_id, Request $request, EntityManagerInterface $em, QuestionnaireRepository $questionnaireRepository): Response
{
    $questionnaire = $questionnaireRepository->find($questionnaire_id);
    
    if (!$questionnaire) {
        throw $this->createNotFoundException('Questionnaire non trouvé');
    }

    $client = $questionnaire->getClient();
    
    // --- ÉTAPE CRUCIALE : Dénormalisation ---
    // On enregistre les infos du client DIRECTEMENT dans le questionnaire 
    // pour faciliter les futurs filtres dans le backoffice.
    $questionnaire->setNom($client->getNom());
    $questionnaire->setPrenom($client->getPrenom());
    // ----------------------------------------

    $rendezVous = new RendezVous();
    $rendezVous->setQuestionnaire($questionnaire);
    $rendezVous->setStatus("en attente");

    $form = $this->createForm(RendezVousType::class, $rendezVous);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $em->persist($rendezVous);
        // On n'oublie pas de persister le questionnaire car ses données (nom/prenom) ont changé
        $em->persist($questionnaire); 
        $em->flush();

        return $this->redirectToRoute('rendezvous_list', ['client_id' => $client->getId()]);
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

    $queryBuilder = $rvRepository->createQueryBuilder('rv')
        ->join('rv.questionnaire', 'q')
        ->where('q.client = :client_id')
        ->andWhere('rv.status != :status_annule')
        ->setParameter('client_id', $client_id)
        ->setParameter('status_annule', 'annulé')
        ->orderBy('rv.date_don', 'DESC');

    if ($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();

        if ($data['campagne']) {
            $queryBuilder->andWhere('q.campagne = :campagne')->setParameter('campagne', $data['campagne']);
        }

        if ($data['status']) {
            $queryBuilder->andWhere('rv.status = :status')->setParameter('status', $data['status']);
        }
      // Filtre sur la DATE
   // Filtre sur la DATE (ex: 2026-02-08)
    if ($data['filter_date']) {
        $queryBuilder->andWhere('rv.date_don LIKE :d')
                     ->setParameter('d', $data['filter_date']->format('Y-m-d') . '%');
    }

    // Filtre sur l'HEURE (Format 24h, ex: 14:00)
    if ($data['filter_time']) {
        // On utilise LIKE avec des jokers pour isoler l'heure et les minutes dans le DATETIME
        $queryBuilder->andWhere('rv.date_don LIKE :t')
                     ->setParameter('t', '%' . $data['filter_time']->format('H:i') . '%');
    }
        // if ($data['date']) {
        //     $dt = $data['date'];
        //     // Logique identique au Back : filtrage à la minute près
        //     $start = (clone $dt)->setTime((int)$dt->format('H'), (int)$dt->format('i'), 0);
        //     $end = (clone $dt)->setTime((int)$dt->format('H'), (int)$dt->format('i'), 59);
            
        //     $queryBuilder->andWhere('rv.date_don BETWEEN :s AND :e')
        //                  ->setParameter('s', $start)
        //                  ->setParameter('e', $end);
        // }
         // LOGIQUE DE TRI UNIQUE
        if (!empty($data['tri_date'])) {
        $parts = explode('_', $data['tri_date']);
        $direction = $parts[1]; // 'ASC' ou 'DESC'
        $queryBuilder->orderBy('rv.date_don', $direction);
        }
    }

    return $this->render('rendez_vous/list.html.twig', [
        'rendezvous' => $queryBuilder->getQuery()->getResult(),
        'filterForm' => $form->createView(),
        'client_id' => $client_id
    ]);
}
     #[Route('/rendez_vous/update/{id}', name:'rendezvous_update')]
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
    public function delete($id, EntityManagerInterface $em, RendezVousRepository $rendezVousRepository){
        $rendezvous = $rendezVousRepository->find($id);
        $clientId = $rendezvous->getQuestionnaire()->getClient()->getId();
        $rendezvous->setStatus('annulé');
        $em->flush();
        return $this->redirectToRoute('rendezvous_list',  ['client_id' => $clientId]);

    }

    //-------------------------------------------backoffice--------------------------------------------------------------//
// src/Controller/RendezVousController.php

#[Route('/backoffice/rendezvous', name: 'rendezvousback_list')]
public function listback(Request $request, RendezVousRepository $rendezVousRepository): Response
{
    $form = $this->createForm(RendezVousFilterType::class);
    $form->handleRequest($request);

    $queryBuilder = $rendezVousRepository->createQueryBuilder('rv')
        ->leftJoin('rv.questionnaire', 'q')
        ->leftJoin('q.campagne', 'c')
        ->leftJoin('c.entities', 'e');

        if ($form->isSubmitted() && $form->isValid()) {
        $data = $form->getData();

        // Filtres texte (depuis les données copiées dans le questionnaire)
        if (!empty($data['nom'])) {
            $queryBuilder->andWhere('q.nom LIKE :nom')->setParameter('nom', '%' . $data['nom'] . '%');
        }
        if (!empty($data['prenom'])) {
            $queryBuilder->andWhere('q.prenom LIKE :prenom')->setParameter('prenom', '%' . $data['prenom'] . '%');
        }

        // Filtres relations
        if (!empty($data['campagne'])) {
            $queryBuilder->andWhere('q.campagne = :campagne')->setParameter('campagne', $data['campagne']);
        }
        if (!empty($data['entite'])) {
            $queryBuilder->andWhere('e = :entite') 
                         ->setParameter('entite', $data['entite']);
        }

        // Filtre Statut
        if (!empty($data['status'])) {
            $queryBuilder->andWhere('rv.status = :status')->setParameter('status', $data['status']);
        }
         // Filtre sur la DATE
   // Filtre sur la DATE (ex: 2026-02-08)
    if ($data['filter_date']) {
        $queryBuilder->andWhere('rv.date_don LIKE :d')
                     ->setParameter('d', $data['filter_date']->format('Y-m-d') . '%');
    }

    // Filtre sur l'HEURE (Format 24h, ex: 14:00)
    if ($data['filter_time']) {
        // On utilise LIKE avec des jokers pour isoler l'heure et les minutes dans le DATETIME
        $queryBuilder->andWhere('rv.date_don LIKE :t')
                     ->setParameter('t', '%' . $data['filter_time']->format('H:i') . '%');
    }
        // // Filtre Date & Heure exacte (Minute par minute)
        // if (!empty($data['date'])) {
        //     $dt = $data['date'];
        //     // On crée une plage de 59 secondes pour ignorer les secondes stockées en BDD
        //     $start = (clone $dt)->setTime((int)$dt->format('H'), (int)$dt->format('i'), 0);
        //     $end = (clone $dt)->setTime((int)$dt->format('H'), (int)$dt->format('i'), 59);
            
        //     $queryBuilder->andWhere('rv.date_don BETWEEN :s AND :e')
        //                  ->setParameter('s', $start)
        //                  ->setParameter('e', $end);
        // }
       // LOGIQUE DE TRI UNIQUE
       if (!empty($data['tri'])) {
        $parts = explode('_', $data['tri']);
        $type = $parts[0];      // 'id' ou 'date'
        $direction = $parts[1]; // 'ASC' ou 'DESC'

        if ($type === 'id') {
            $queryBuilder->orderBy('rv.id', $direction);
        } else {
            // Trie par Date ET par Heure simultanément
            $queryBuilder->orderBy('rv.date_don', $direction);
        }
    }}
    return $this->render('rendez_vous/listback.html.twig', [
        'rendezvous' => $queryBuilder->getQuery()->getResult(),
        'filterForm' => $form->createView(),
    ]);
}

    #[Route('/backoffice/rendez_vous/update/{id}', name:'rendezvousback_update')]
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
    public function deleteback($id, EntityManagerInterface $em, RendezVousRepository $rendezVousRepository){
        $rendezvous = $rendezVousRepository->find($id);
        $em->remove($rendezvous);
        $em->flush();
        return $this->redirectToRoute('rendezvousback_list');

    }

   
}
