<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Form\PasswordResetRequestType;
use App\Form\PasswordResetType;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormError;

#[Route('/password-reset')]
class PasswordResetController extends AbstractController
{
  private string $recaptchaSiteKey;
  private string $recaptchaSecretKey;

  public function __construct(
    string $recaptchaSiteKey,
    string $recaptchaSecretKey
  ) {
    $this->recaptchaSiteKey = $recaptchaSiteKey;
    $this->recaptchaSecretKey = $recaptchaSecretKey;
  }

  #[Route('/request', name: 'app_password_reset_request', methods: ['GET', 'POST'])]
  public function request(
    Request $request,
    UserRepository $userRepository,
    EntityManagerInterface $entityManager,
    MailerInterface $mailer,
    PasswordResetTokenRepository $tokenRepository
  ): Response {
    $form = $this->createForm(PasswordResetRequestType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $hasErrors = false;

      $email = $form->get('email')->getData();
      if (empty($email)) {
        $form->get('email')->addError(new FormError('email cannot be empty'));
        $hasErrors = true;
      } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form->get('email')->addError(new FormError('please enter a valid email address'));
        $hasErrors = true;
      }

      $recaptchaResponse = $request->request->get('g-recaptcha-response');
      if (empty($recaptchaResponse)) {
        $this->addFlash('error', 'Please complete the reCAPTCHA verification');
        $hasErrors = true;
      } else {
        $recaptcha = new ReCaptcha($this->recaptchaSecretKey);
        $resp = $recaptcha->verify($recaptchaResponse, $request->getClientIp());

        if (!$resp->isSuccess()) {
          $this->addFlash('error', 'reCAPTCHA verification failed. Please try again.');
          $hasErrors = true;
        }
      }

      if (!$hasErrors) {
        $user = $userRepository->findOneBy(['email' => $email]);

        $this->addFlash('success', 'If an account exists with this email, you will receive a password reset link shortly.');

        if ($user) {
          $tokenRepository->deleteUserTokens($user);

          $token = bin2hex(random_bytes(32));
          $resetToken = new PasswordResetToken();
          $resetToken->setUser($user);
          $resetToken->setToken($token);

          $entityManager->persist($resetToken);
          $entityManager->flush();

          $resetUrl = $this->generateUrl('app_password_reset_confirm', [
            'token' => $token
          ], UrlGeneratorInterface::ABSOLUTE_URL);

          $emailMessage = (new TemplatedEmail())
            ->from('khalilboujemaa2@gmail.com')
            ->to($user->getEmail())
            ->subject('Password Reset Request - BloodLink')
            ->htmlTemplate('password_reset/email.html.twig')
            ->context([
              'user' => $user,
              'resetUrl' => $resetUrl,
              'expiresAt' => $resetToken->getExpiresAt(),
            ]);

          try {
            $mailer->send($emailMessage);
          } catch (\Exception $e) {
            error_log('Email send error: ' . $e->getMessage());
          }
        }

        return $this->redirectToRoute('app_login');
      }
    }

    return $this->render('password_reset/request.html.twig', [
      'form' => $form,
      'recaptcha_site_key' => $this->recaptchaSiteKey,
    ]);
  }

  #[Route('/reset/{token}', name: 'app_password_reset_confirm', methods: ['GET', 'POST'])]
  public function reset(
    string $token,
    Request $request,
    PasswordResetTokenRepository $tokenRepository,
    EntityManagerInterface $entityManager,
    UserPasswordHasherInterface $passwordHasher
  ): Response {
    $resetToken = $tokenRepository->findValidToken($token);

    if (!$resetToken || $resetToken->isExpired() || $resetToken->isUsed()) {
      $this->addFlash('error', 'This password reset link is invalid or has expired.');
      return $this->redirectToRoute('app_password_reset_request');
    }

    $form = $this->createForm(PasswordResetType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $hasErrors = false;

      $password = $form->get('password')->getData();
      if (empty($password)) {
        $form->get('password')->first->addError(new FormError('password cannot be empty'));
        $hasErrors = true;
      } elseif (strlen($password) < 6) {
        $form->get('password')->first->addError(new FormError('password must be at least 6 characters long'));
        $hasErrors = true;
      } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).+$/', $password)) {
        $form->get('password')->first->addError(new FormError('password must contain at least one letter and one number'));
        $hasErrors = true;
      }

      if (!$hasErrors) {
        $user = $resetToken->getUser();
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $resetToken->setUsed(true);

        $entityManager->flush();

        $this->addFlash('success', 'Your password has been successfully reset. You can now log in with your new password.');
        return $this->redirectToRoute('app_login');
      }
    }

    return $this->render('password_reset/reset.html.twig', [
      'form' => $form,
      'token' => $token,
    ]);
  }
}
