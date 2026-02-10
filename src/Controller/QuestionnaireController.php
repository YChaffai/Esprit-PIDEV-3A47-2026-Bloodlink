<?php

namespace App\Controller;

use App\Form\QuestionnaireType;
use App\Form\UpdateQuestionnaireType;

use App\Form\QuestionnaireFilterType;
use App\Form\CreateQuestionnaireBackType;
use App\Entity\Questionnaire;
use App\Repository\QuestionnaireRepository;
use App\Repository\ClientRepository;
use App\Repository\CampagneRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class QuestionnaireController extends AbstractController
{
  //-------------------------------------------frontoffice--------------------------------------------------------------//
  #[Route('/questionnaire/new/{id}/{client_id}',  name: 'questionnaire_new')]
  #[IsGranted('ROLE_CLIENT')]
  public function new(int $id, int $client_id, Request $request, CampagneRepository $campagneRepo,  ClientRepository $clientRepository, EntityManagerInterface $em, QuestionnaireRepository $questionnaireRepo)
  {
    $campagne = $campagneRepo->find($id);
    $client = $clientRepository->find($client_id);
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
  public function list(Request $request, int $client_id, QuestionnaireRepository $questionnaireRepository)
  {
    // 1. On crée le formulaire de filtre
    $form = $this->createForm(QuestionnaireFilterType::class);
    $form->handleRequest($request);

    // 2. On prépare le QueryBuilder pour la liste du Backoffice
    $queryBuilder = $questionnaireRepository->createQueryBuilder('q')
      ->where('q.client = :client_id') // Sécurité : on filtre par le client de l'URL
      ->setParameter('client_id', $client_id)
      ->leftJoin('q.campagne', 'c')
      ->orderBy('q.date', 'DESC');

    // 3. On applique les filtres si le formulaire est soumis (GET)
    if ($form->isSubmitted() && $form->isValid()) {
      $data = $form->getData();
      // Filtrer par DATE uniquement

      if (!empty($data['campagne'])) {
        $queryBuilder->andWhere('q.campagne = :campagne')
          ->setParameter('campagne', $data['campagne']);
      }

      // Filtre sur la DATE
      if ($data['filter_date']) {
        $queryBuilder->andWhere('q.date LIKE :d')
          ->setParameter('d', $data['filter_date']->format('Y-m-d') . '%');
      }

      // Filtre sur l'HEURE (Format 24h, ex: 14:00)
      if ($data['filter_time']) {
        // On utilise LIKE avec des jokers pour isoler l'heure et les minutes dans le DATETIME
        $queryBuilder->andWhere('q.date LIKE :t')
          ->setParameter('t', '%' . $data['filter_time']->format('H:i') . '%');
      }
    }

    // LOGIQUE DE TRI UNIQUE
    if (!empty($data['tri_date'])) {
      $parts = explode('_', $data['tri_date']);
      $direction = $parts[1]; // 'ASC' ou 'DESC'
      $queryBuilder->orderBy('q.date', $direction);
    }

    return $this->render('questionnaire/list.html.twig', [
      'questionnaires' => $queryBuilder->getQuery()->getResult(),
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
    return $this->render('questionnaire/update.html.twig', [
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

    // 2. On prépare le QueryBuilder pour la liste du Backoffice
    $queryBuilder = $questionnaireRepository->createQueryBuilder('q')
      ->leftJoin('q.campagne', 'c')
      ->orderBy('q.date', 'DESC');

    // 3. On applique les filtres si le formulaire est soumis (GET)
    if ($form->isSubmitted() && $form->isValid()) {
      $data = $form->getData();

      if (!empty($data['nom'])) {
        $queryBuilder->andWhere('q.nom LIKE :nom')
          ->setParameter('nom', '%' . $data['nom'] . '%');
      }
      if (!empty($data['prenom'])) {
        $queryBuilder->andWhere('q.prenom LIKE :prenom')
          ->setParameter('prenom', '%' . $data['prenom'] . '%');
      }

      if (!empty($data['campagne'])) {
        $queryBuilder->andWhere('q.campagne = :campagne')
          ->setParameter('campagne', $data['campagne']);
      }

      if (!empty($data['groupSanguin'])) {
        $queryBuilder->andWhere('q.group_sanguin = :gs')
          ->setParameter('gs', $data['groupSanguin']);
      }
      // Filtre Date
      if ($data['filter_date']) {
        $queryBuilder->andWhere('q.date LIKE :d')
          ->setParameter('d', $data['filter_date']->format('Y-m-d') . '%');
      }

      // Filtre Heure (Format 24h en base de données)
      if ($data['filter_time']) {
        $queryBuilder->andWhere('q.date LIKE :t')
          ->setParameter('t', '%' . $data['filter_time']->format('H:i') . '%');
      }
      if (!empty($data['tri'])) {
        $parts = explode('_', $data['tri']);
        $type = $parts[0];      // 'id' ou 'date'
        $direction = $parts[1]; // 'ASC' ou 'DESC'

        if ($type === 'id') {
          $queryBuilder->orderBy('q.id', $direction);
        } else {
          // Trie par Date ET par Heure simultanément
          $queryBuilder->orderBy('q.date', $direction);
        }
      }
    }

    // 4. On envoie 'filterForm' à la vue listback.html.twig
    return $this->render('questionnaire/listback.html.twig', [
      'questionnaires' => $queryBuilder->getQuery()->getResult(),
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
      $client = $clientRepository->findOneBy(['email' => $clientEmail]); // Trouver le client par email

      if ($client) {
        // Associer la campagne et le client au questionnaire
        $questionnaire->setClient($client);
        $questionnaire->setCampagne($campagne);
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
}
