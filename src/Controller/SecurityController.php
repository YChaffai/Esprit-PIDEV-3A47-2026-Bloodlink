<?php

namespace App\Controller;


use App\Entity\User;
use App\Entity\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Form\UserType;

class SecurityController extends AbstractController
{
  #[Route(path: '/login', name: 'app_login')]
  public function login(AuthenticationUtils $authenticationUtils): Response
  {
    if ($user = $this->getUser()) {
      if (in_array('ROLE_ADMIN', $user->getRoles())) {
        return $this->redirectToRoute('app_user_index');
      } elseif (in_array('ROLE_CLIENT', $user->getRoles())) {
        return $this->redirectToRoute('front_home');
      }
    }

    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    return $this->render('security/login.html.twig', [
      'last_username' => $lastUsername,
      'error' => $error,
    ]);
  }

  #[Route(path: '/logout', name: 'app_logout')]
  public function logout(): void
  {
    throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
  }

  #[Route('/register', name: 'app_register')]
  public function register(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
  {
    $user = new User();
    $user->setRole('client');
    $form = $this->createForm(UserType::class, $user, [
      'is_new' => true,
      'is_registration' => true,
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      // Force client role — prevent privilege escalation
      $user->setRole('client');
      $user->setPassword($passwordHasher->hashPassword($user, $user->getPlainPassword()));

      // Create the associated Client entity with defaults
      $client = new Client();
      $client->setUser($user);
      $client->setTypeSang('O+');
      $client->setDernierDon(new \DateTime('2000-01-01'));

      $em->persist($user);
      $em->persist($client);
      $em->flush();

      $this->addFlash('success', 'Votre compte a été créé avec succès.');

      return $this->redirectToRoute('app_login');
    }

    return $this->render('security/register.html.twig', [
      'form' => $form->createView(),
    ]);
  }
}

