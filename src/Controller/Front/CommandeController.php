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
                // Store commande data in session to survive the Stripe redirect
                $sessionData = [
                    'type_sang' => $commande->getTypeSang(),
                    'quantite' => $commande->getQuantite(),
                    'priorite' => $commande->getPriorite(),
                    'banque_id' => $commande->getBanque()->getId(),
                    'reference' => $commande->getReference(),
                ];
                $request->getSession()->set('pending_commande', $sessionData);

                // Initialize Stripe
                \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

                // Create a Stripe Checkout Session
                $checkout_session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => 'Frais Administratifs - Commande BloodLink',
                            ],
                            'unit_amount' => 800,
                        ],
                        'quantity' => 1,
                    ]],
                    'mode' => 'payment',
                    'success_url' => $this->generateUrl('front_commande_success', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
                    'cancel_url' => $this->generateUrl('front_commande_cancel', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL),
                ]);

                return $this->redirect($checkout_session->url, 303);
            }
        }

        return $this->render('front/AjoutCommande.html.twig', [
            'form' => $form->createView(),
        ], new Response(null, $form->isSubmitted() && !$form->isValid() ? 422 : 200));
    }

    #[Route('/front/commande/success', name: 'front_commande_success', methods: ['GET'])]
    public function checkoutSuccess(Request $request, EntityManagerInterface $em, StockAlloc $alloc, MailerInterface $mailer): Response
    {
        $sessionData = $request->getSession()->get('pending_commande');
        if (!$sessionData) {
            $this->addFlash('error', 'Aucune commande en attente ou session expirée.');
            return $this->redirectToRoute('front_commandes_index');
        }

        $user = $this->getUser();
        /** @var \App\Entity\User $user */
        $client = $user->getClient();
        $banque = $em->getRepository(\App\Entity\Banque::class)->find($sessionData['banque_id']);

        $commande = new Commande();
        $commande->setClient($client);
        $commande->setTypeSang($sessionData['type_sang']);
        $commande->setQuantite($sessionData['quantite']);
        $commande->setPriorite($sessionData['priorite']);
        $commande->setBanque($banque);
        $commande->setReference($sessionData['reference']);
        $commande->setStatus('En Attente');

        try {
            $alloc->assignStockOrThrow($commande);
            $em->persist($commande);
            $em->flush();
            $request->getSession()->remove('pending_commande');

            // Send confirmation email
            $toEmail = $user->getEmail();
            if ($toEmail) {
                $email = (new TemplatedEmail())
                    ->from(new Address('khalilboujemaa2@gmail.com', 'BloodLink'))
                    ->to($toEmail)
                    ->subject('Votre commande a été confirmée #' . $commande->getId())
                    ->htmlTemplate('Email/commande.html.twig')
                    ->context([
                        'commande' => $commande,
                        'clientName' => $user->getNom(),
                    ]);
                $mailer->send($email);
            }

            $this->addFlash('success', 'Paiement réussi ! Commande validée.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Paiement reçu mais erreur système: ' . $e->getMessage());
        }

        return $this->redirectToRoute('front_commandes_index');
    }

    #[Route('/front/commande/cancel', name: 'front_commande_cancel', methods: ['GET'])]
    public function checkoutCancel(Request $request): Response
    {
        $request->getSession()->remove('pending_commande');
        $this->addFlash('warning', 'Le paiement a été annulé. Votre commande n\'a pas été enregistrée.');
        return $this->redirectToRoute('front_commande_new');
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
    #[Route('/front/commande/{id}/valider', name: 'front_commande_valider')]
    public function valider(Commande $commande, EntityManagerInterface $em): Response
    {
        $commande->setStatus('Confirmée');

        $em->flush();

        $this->addFlash('success', 'Commande validée');

        return $this->redirectToRoute('front_demande_index');
    }
}
