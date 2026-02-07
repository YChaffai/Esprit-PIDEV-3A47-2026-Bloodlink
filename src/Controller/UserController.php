<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Client;
use App\Entity\Banque;
use App\Form\UserType;
use App\Form\ClientType;
use App\Form\BanqueType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;

#[Route('/user')]
final class UserController extends AbstractController
{
  #[Route(name: 'app_user_index', methods: ['GET'])]
  public function index(UserRepository $userRepository): Response
  {
    return $this->render('user/index.html.twig', [
      'users' => $userRepository->findAll(),
    ]);
  }

  #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
  public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher
  ): Response {
    $user = new User();
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $hasErrors = false;

      if (empty($user->getNom())) {
        $form->get('nom')->addError(new FormError('last name cannot be empty'));
        $hasErrors = true;
      }

      if (empty($user->getPrenom())) {
        $form->get('prenom')->addError(new FormError('first name cannot be empty'));
        $hasErrors = true;
      }

      $email = $user->getEmail();
      if (empty($email)) {
        $form->get('email')->addError(new FormError('email cannot be empty'));
        $hasErrors = true;
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form->get('email')->addError(new FormError('email must be exemple@exemple.xyz'));
        $hasErrors = true;
      }

      if (empty($user->getRole())) {
        $form->get('role')->addError(new FormError('role must be selected'));
        $hasErrors = true;
      }

      $plainPassword = $form->get('password')->getData();
      if (empty($plainPassword)) {
        $form->get('password')->addError(new FormError('password cannot be empty'));
        $hasErrors = true;
      } elseif (strlen($plainPassword) < 6) {
        $form->get('password')->addError(new FormError('password must be at least 6 characters long'));
        $hasErrors = true;
      } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).+$/', $plainPassword)) {
        $form->get('password')->addError(new FormError('Password must contain at least one letter and one number'));
        $hasErrors = true;
      }

      if (!$hasErrors) {
        $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));

        $entityManager->persist($user);
        $entityManager->flush();

        if ($user->getRole() === 'client') {
          return $this->redirectToRoute('app_user_complete_client', ['id' => $user->getId()]);
        } elseif ($user->getRole() === 'banque') {
          return $this->redirectToRoute('app_user_complete_banque', ['id' => $user->getId()]);
        }

        $this->addFlash('success', 'User created successfully!');
        return $this->redirectToRoute('app_user_index');
      }
    }

    return $this->render('user/new.html.twig', [
      'form' => $form,
      'user' => $user,
    ]);
  }

  #[Route('/{id}/complete-client', name: 'app_user_complete_client', methods: ['GET', 'POST'])]
  public function completeClient(
    Request $request,
    User $user,
    EntityManagerInterface $entityManager
  ): Response {
    if ($user->getClient()) {
      $this->addFlash('info', 'Client information already exists.');
      return $this->redirectToRoute('app_user_index');
    }

    $client = new Client();
    $client->setUser($user);

    $form = $this->createForm(ClientType::class, $client);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $hasErrors = false;

      if (empty($client->getTypeSang())) {
        $form->get('typeSang')->addError(new FormError('blood type must be selected'));
        $hasErrors = true;
      }

      if (empty($client->getDernierDon())) {
        $form->get('dernierDon')->addError(new FormError('last donation date cannot be empty'));
        $hasErrors = true;
      } elseif ($client->getDernierDon() > new \DateTime()) {
        $form->get('dernierDon')->addError(new FormError('last donation date cannot be in the future'));
        $hasErrors = true;
      }

      if (!$hasErrors) {
        $entityManager->persist($client);
        $entityManager->flush();

        $this->addFlash('success', 'Client information completed successfully!');
        return $this->redirectToRoute('app_user_index');
      }
    }

    return $this->render('user/complete_client.html.twig', [
      'form' => $form,
      'user' => $user,
    ]);
  }

  #[Route('/{id}/complete-banque', name: 'app_user_complete_banque', methods: ['GET', 'POST'])]
  public function completeBanque(
    Request $request,
    User $user,
    EntityManagerInterface $entityManager
  ): Response {
    if ($user->getBanque()) {
      $this->addFlash('info', 'Banque information already exists.');
      return $this->redirectToRoute('app_user_index');
    }

    $banque = new Banque();
    $banque->setUser($user);

    $form = $this->createForm(BanqueType::class, $banque);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $hasErrors = false;

      if (empty($banque->getNom())) {
        $form->get('nom')->addError(new FormError('bank name cannot be empty'));
        $hasErrors = true;
      }

      if (empty($banque->getAdresse())) {
        $form->get('adresse')->addError(new FormError('address cannot be empty'));
        $hasErrors = true;
      }

      $telephone = $banque->getTelephone();
      if (empty($telephone)) {
        $form->get('telephone')->addError(new FormError('phone number cannot be empty'));
        $hasErrors = true;
      } elseif (!preg_match('/^\d{8}$/', $telephone)) {
        $form->get('telephone')->addError(new FormError('phone number must be exactly 8 digits'));
        $hasErrors = true;
      }

      if (!$hasErrors) {
        $entityManager->persist($banque);
        $entityManager->flush();

        $this->addFlash('success', 'Banque information completed successfully!');
        return $this->redirectToRoute('app_user_index');
      }
    }

    return $this->render('user/complete_banque.html.twig', [
      'form' => $form,
      'user' => $user,
    ]);
  }

  #[Route('/{id}/edit-client', name: 'app_user_edit_client', methods: ['GET', 'POST'])]
  public function editClient(
    Request $request,
    User $user,
    EntityManagerInterface $entityManager
  ): Response {
    $client = $user->getClient();

    if (!$client) {
      $this->addFlash('error', 'No client information found for this user.');
      return $this->redirectToRoute('app_user_index');
    }

    $form = $this->createForm(ClientType::class, $client);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $hasErrors = false;

      if (empty($client->getTypeSang())) {
        $form->get('typeSang')->addError(new FormError('blood type must be selected'));
        $hasErrors = true;
      }

      if (empty($client->getDernierDon())) {
        $form->get('dernierDon')->addError(new FormError('last donation date cannot be empty'));
        $hasErrors = true;
      } elseif ($client->getDernierDon() > new \DateTime()) {
        $form->get('dernierDon')->addError(new FormError('last donation date cannot be in the future'));
        $hasErrors = true;
      }

      if (!$hasErrors) {
        $entityManager->flush();

        $this->addFlash('success', 'Client information updated successfully!');
        return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
      }
    }

    return $this->render('user/edit_client.html.twig', [
      'form' => $form,
      'user' => $user,
      'client' => $client,
    ]);
  }

  #[Route('/{id}/edit-banque', name: 'app_user_edit_banque', methods: ['GET', 'POST'])]
  public function editBanque(
    Request $request,
    User $user,
    EntityManagerInterface $entityManager
  ): Response {
    $banque = $user->getBanque();

    if (!$banque) {
      $this->addFlash('error', 'No banque information found for this user.');
      return $this->redirectToRoute('app_user_index');
    }

    $form = $this->createForm(BanqueType::class, $banque);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $hasErrors = false;

      if (empty($banque->getNom())) {
        $form->get('nom')->addError(new FormError('bank name cannot be empty'));
        $hasErrors = true;
      }

      if (empty($banque->getAdresse())) {
        $form->get('adresse')->addError(new FormError('address cannot be empty'));
        $hasErrors = true;
      }

      $telephone = $banque->getTelephone();
      if (empty($telephone)) {
        $form->get('telephone')->addError(new FormError('phone number cannot be empty'));
        $hasErrors = true;
      } elseif (!preg_match('/^\d{8}$/', $telephone)) {
        $form->get('telephone')->addError(new FormError('phone number must be exactly 8 digits'));
        $hasErrors = true;
      }

      if (!$hasErrors) {
        $entityManager->flush();

        $this->addFlash('success', 'Banque information updated successfully!');
        return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
      }
    }

    return $this->render('user/edit_banque.html.twig', [
      'form' => $form,
      'user' => $user,
      'banque' => $banque,
    ]);
  }

  #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
  public function show(User $user): Response
  {
    return $this->render('user/show.html.twig', [
      'user' => $user,
    ]);
  }

  #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
  public function edit(
    Request $request,
    User $user,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher
  ): Response {
    $form = $this->createForm(UserType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $hasErrors = false;

      if (empty($user->getNom())) {
        $form->get('nom')->addError(new FormError('last name cannot be empty'));
        $hasErrors = true;
      }

      if (empty($user->getPrenom())) {
        $form->get('prenom')->addError(new FormError('first name cannot be empty'));
        $hasErrors = true;
      }

      $email = $user->getEmail();
      if (empty($email)) {
        $form->get('email')->addError(new FormError('email cannot be empty'));
        $hasErrors = true;
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form->get('email')->addError(new FormError('email must be exemple@exemple.xyz'));
        $hasErrors = true;
      }

      if (empty($user->getRole())) {
        $form->get('role')->addError(new FormError('role must be selected'));
        $hasErrors = true;
      }

      $plainPassword = $form->get('password')->getData();
      if (!empty($plainPassword)) {
        if (strlen($plainPassword) < 6) {
          $form->get('password')->addError(new FormError('password must be at least 6 characters long'));
          $hasErrors = true;
        } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).+$/', $plainPassword)) {
          $form->get('password')->addError(new FormError('Password must contain at least one letter and one number'));
          $hasErrors = true;
        }
      }

      if (!$hasErrors) {
        if (!empty($plainPassword)) {
          $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
        }

        $entityManager->flush();

        $this->addFlash('success', 'User updated successfully!');
        return $this->redirectToRoute('app_user_index');
      }
    }

    return $this->render('user/edit.html.twig', [
      'user' => $user,
      'form' => $form,
    ]);
  }

  #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
  public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
  {
    if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
      $entityManager->remove($user);
      $entityManager->flush();
      $this->addFlash('success', 'User deleted successfully!');
    }

    return $this->redirectToRoute('app_user_index');
  }
}
