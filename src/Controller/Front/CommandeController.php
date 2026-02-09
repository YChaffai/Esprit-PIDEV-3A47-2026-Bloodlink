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
    ): Response {
        $commande = new Commande();
        $commande->setStatus('En Attente');

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                $alloc->assignStockOrThrow($commande);
            } catch (\RuntimeException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $client = $commande->getClient(); // adjust
            $toEmail = $client?->getMail();
            $em->persist($commande);
            $em->flush();

            $this->addFlash('success', 'Commande Créée.');
            return $this->redirectToRoute('front_commandes_index');
        }

        return $this->render('Front/AjoutCommande.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
