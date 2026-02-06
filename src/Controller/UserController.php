<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
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

        $this->addFlash('success', 'User created successfully!');
        return $this->redirectToRoute('app_user_index');
      }
    }

    return $this->render('user/new.html.twig', [
      'form' => $form,
      'user' => $user,
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
