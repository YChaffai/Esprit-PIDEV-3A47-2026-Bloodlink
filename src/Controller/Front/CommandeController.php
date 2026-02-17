<?php

namespace App\Controller\Front;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Service\StockAlloc;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class CommandeController extends AbstractController
{
  #[Route('/front/commande', name: 'front_commandes_index', methods: ['GET'])]
  public function index(CommandeRepository $commandeRepository): Response
  {
    $commandes = $commandeRepository->findBy([], ['id' => 'DESC']);

    return $this->render('front/Commande.html.twig', [
      'commandes' => $commandes,
    ]);
  }

  #[Route('/front/new', name: 'front_commande_new', methods: ['GET', 'POST'])]
  public function new(
    Request $request,
    EntityManagerInterface $em,
    StockAlloc $alloc,
    MailerInterface $mailer,
  ): Response {
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }

    /** @var \App\Entity\User $user */
    $client = $user->getClient();

    if (!$client) {
        $this->addFlash('warning', 'Vous devez compléter votre profil client pour commander.');
        return $this->redirectToRoute('front_commandes_index'); 
    }

    $commande = new Commande();
    $commande->setStatus('En Attente');
    $commande->setClient($client); 

    $form = $this->createForm(CommandeType::class, $commande, ['show_status' => false]);
    
    
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
      try {
        $alloc->assignStockOrThrow($commande);
      } catch (\RuntimeException $e) {
        $errorMsg = $e->getMessage();
        if (str_contains(strtolower($errorMsg), 'banque')) {
          $form->get('banque')->addError(new FormError($errorMsg));
        } else {
          $form->get('quantite')->addError(new FormError($errorMsg));
        }
      }

      if ($form->getErrors(true)->count() === 0) {
        $em->persist($commande);
        $em->flush();

        $toEmail = $user->getEmail();

        if (!$toEmail) {
           $this->addFlash('danger', 'Commande created but no email found.');
           return $this->redirectToRoute('front_commandes_index');
        }

        $email = (new TemplatedEmail())
          ->from(new Address('khalilboujemaa2@gmail.com', 'BloodLink'))
          ->to($toEmail)
          ->subject('Votre commande a été créée #' . $commande->getId())
          ->htmlTemplate('Email/commande.html.twig')
          ->context([
            'commande' => $commande,
            'clientName' => $user->getNom(), 
          ]);

        try {
          $mailer->send($email);
          $this->addFlash('success', 'Commande Créée + Email sent.');
        } catch (\Throwable $e) {
          $this->addFlash('warning', 'Commande Créée but email failed: ' . $e->getMessage());
        }
      
        return $this->redirectToRoute('front_commandes_index');
      }
    }

    return $this->render('front/AjoutCommande.html.twig', [
      'form' => $form->createView(),
    ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
  }

  #[Route('/front/commande/{id}/pdf', name: 'front_commande_pdf', methods: ['GET'])]
  public function downloadPdf(Commande $commande): Response
  {
      $user = $this->getUser();
      if (!$user) {
          return $this->redirectToRoute('app_login');
      }

      /** @var \App\Entity\User $user */
      $client = $user->getClient();

      if ($commande->getClient() !== $client) {
          throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette commande.');
      }

      $pdfOptions = new \Dompdf\Options();
      $pdfOptions->set('defaultFont', 'Arial');
      $pdfOptions->set('isRemoteEnabled', true); 

      $dompdf = new \Dompdf\Dompdf($pdfOptions);

      $html = $this->renderView('front/commande_pdf.html.twig', [
          'commande' => $commande,
          'user' => $user
      ]);

      $dompdf->loadHtml($html);
      $dompdf->setPaper('A4', 'portrait');
      $dompdf->render();

      return new Response($dompdf->output(), 200, [
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'attachment; filename="commande-' . $commande->getReference() . '.pdf"',
      ]);
  }
}
