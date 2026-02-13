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
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    PasswordResetTokenRepository $tokenRepository,
    ValidatorInterface $validator
  ): Response {
    $form = $this->createForm(PasswordResetRequestType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $email = $form->get('email')->getData();

      $emailErrors = $validator->validatePropertyValue(
        'App\Entity\User',
        'email',
        $email
      );

      if (count($emailErrors) > 0) {
        foreach ($emailErrors as $error) {
          $form->get('email')->addError(new FormError($error->getMessage()));
        }
      } else {
        $hasReCaptchaError = false;
        if (!empty($this->recaptchaSecretKey) && $this->recaptchaSecretKey !== 'your_secret_key') {
          $recaptchaResponse = $request->request->get('g-recaptcha-response');
          if (empty($recaptchaResponse)) {
            $this->addFlash('error', 'Veuillez valider le reCAPTCHA.');
            $hasReCaptchaError = true;
          } else {
            $recaptcha = new ReCaptcha($this->recaptchaSecretKey);
            $resp = $recaptcha->verify($recaptchaResponse, $request->getClientIp());

            if (!$resp->isSuccess()) {
              $this->addFlash('error', 'Échec du reCAPTCHA. Veuillez réessayer.');
              $hasReCaptchaError = true;
            }
          }
        }

        if (!$hasReCaptchaError && $form->isValid()) {
          $user = $userRepository->findOneBy(['email' => $email]);

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
              ->from(new Address('khalilboujemaa2@gmail.com', 'BloodLink'))
              ->to(new Address($user->getEmail(), $user->getPrenom() . ' ' . $user->getNom()))
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

          $this->addFlash('success', 'Si ce compte existe, un lien vous sera envoyé sous peu.');
          return $this->redirectToRoute('app_login');
        }
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
      $this->addFlash('error', 'Lien de réinitialisation invalide ou expiré.');
      return $this->redirectToRoute('app_password_reset_request');
    }

    $form = $this->createForm(PasswordResetType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
      $hasErrors = false;

      $password = $form->get('password')->getData();

      if (empty($password)) {
        $form->get('password')->addError(new FormError('Le mot de passe est requis'));
        $hasErrors = true;
      } elseif (strlen($password) < 6) {
        $form->get('password')->addError(new FormError('6 caractères minimum requis'));
        $hasErrors = true;
      } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).+$/', $password)) {
        $form->get('password')->addError(new FormError('Doit contenir une lettre et un chiffre'));
        $hasErrors = true;
      }

      if (!$hasErrors && $form->isValid()) {
        $user = $resetToken->getUser();
        $user->setPassword($passwordHasher->hashPassword($user, $password));

        $resetToken->setUsed(true);

        $entityManager->flush();

        $this->addFlash('success', 'Mot de passe réinitialisé. Vous pouvez maintenant vous connecter.');
        return $this->redirectToRoute('app_login');
      }
    }

    return $this->render('password_reset/reset.html.twig', [
      'form' => $form,
      'token' => $token,
    ]);
  }
}
