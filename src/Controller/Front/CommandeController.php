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
    $commande = new Commande();
    $commande->setStatus('En Attente');

    $form = $this->createForm(CommandeType::class, $commande);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        try {
            $alloc->assignStockOrThrow($commande);
        } catch (\RuntimeException $e) {
            $errorMsg = $e->getMessage();
            // Attach to banque if mentioned, otherwise to quantite
            if (str_contains(strtolower($errorMsg), 'banque')) {
                $form->get('banque')->addError(new FormError($errorMsg));
            } else {
                $form->get('quantite')->addError(new FormError($errorMsg));
            }
        }
    }

    if ($form->isSubmitted() && $form->isValid()) {
        // Save first
        $em->persist($commande);
        $em->flush();

        // Get recipient safely
        $client = $commande->getClient();
        $toEmail = $client?->getUser()?->getEmail();

        if (!$toEmail) {
            $this->addFlash('danger', 'Commande created but no email found for this client.');
            return $this->redirectToRoute('front_commandes_index');
        }

        $email = (new TemplatedEmail())
            ->from(new Address('yassinechaffai4@gmail.com', 'Pidev'))
            ->to($toEmail)
            ->subject('Votre commande a été créée #' . $commande->getId())
            ->htmlTemplate('Email/commande.html.twig')
            ->context([
                'commande' => $commande,
                'clientName' => $client?->getUser()?->getNom(),
            ]);

        try {
            $mailer->send($email);
            $this->addFlash('success', 'Commande Créée + Email sent.');
        } catch (\Throwable $e) {
            $this->addFlash('warning', 'Commande Créée but email failed: ' . $e->getMessage());
        }

        return $this->redirectToRoute('front_commandes_index');
    }

    return $this->render('Front/AjoutCommande.html.twig', [
        'form' => $form->createView(),
    ]);
}
}
