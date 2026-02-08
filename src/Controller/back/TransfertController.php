<?php

namespace App\Controller\back;

use App\Entity\Transfert;
use App\Entity\Demande;
use App\Form\TransfertType;
use App\Repository\TransfertRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\StockRepository;
use App\Entity\Stock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/transfert')]
class TransfertController extends AbstractController
{
    #[Route('/', name: 'back_transfert_index', methods: ['GET'])]
    public function index(TransfertRepository $repo): Response
    {
        return $this->render('back/transfert.html.twig', [
            'transferts' => $repo->findAll(),
        ]);
    }

    #[Route('/valider/{id}', name: 'back_transfert_valider')]
public function valider(
    Demande $demande,
    EntityManagerInterface $em,
    StockRepository $stockRepo
): Response
{
    // Vérifier que la demande est toujours en attente
    if ($demande->getStatus() !== 'EN_ATTENTE') {
        $this->addFlash('warning', 'Cette demande a déjà été traitée.');
        return $this->redirectToRoute('back_transfert_index');
    }

    // 🔹 Stock CNTS (id = 1)
    $stockCNTS = $stockRepo->find(1);

    if (!$stockCNTS) {
        $this->addFlash('danger', 'Stock CNTS introuvable.');
        return $this->redirectToRoute('back_transfert_index');
    }

    // Vérifier que la quantité demandée est disponible
    if ($demande->getQuantite() > $stockCNTS->getQuantite()) {
        $this->addFlash('danger', 'Stock CNTS insuffisant : il reste ' . $stockCNTS->getQuantite() . ' unités.');
        return $this->redirectToRoute('back_transfert_index');
    }

    // 🔹 Stock de la banque destinataire
    $stockBanque = $stockRepo->findOneBy([
        'typeSang' => $demande->getTypeSang(),
        'typeOrgId' => $demande->getIdBanque() // Banque destinataire
    ]);

    if (!$stockBanque) {
        $this->addFlash('danger', 'Stock de la banque introuvable pour ce type de sang.');
        return $this->redirectToRoute('back_transfert_index');
    }

    // 🔹 Créer le transfert
    $transfert = new Transfert();
    $transfert->setDemande($demande);
    $transfert->setStock($stockCNTS); // CNTS comme source
    $transfert->setFromOrgId(1);
    $transfert->setFromOrg('CNTS');
    $transfert->setToOrgId($demande->getIdBanque());
    $transfert->setToOrg('Banque ' . $demande->getIdBanque());
    $transfert->setQuantite($demande->getQuantite());
    $transfert->setDateEnvoie(new \DateTime());
    $transfert->setDateReception(new \DateTime());
    $transfert->setStatus('EN_COURS');
    $transfert->setCreatedAt(new \DateTimeImmutable());

    // 🔹 Décrémenter le stock CNTS
    $stockCNTS->setQuantite($stockCNTS->getQuantite() - $demande->getQuantite());
    $stockCNTS->setUpdatedAt(new \DateTimeImmutable());

    // 🔹 Incrémenter le stock de la banque
    $stockBanque->setQuantite($stockBanque->getQuantite() + $demande->getQuantite());
    $stockBanque->setUpdatedAt(new \DateTimeImmutable());

    // Persister et flusher
    $em->persist($transfert);
    $em->flush();

    // Mettre à jour le status de la demande
    $demande->setStatus('VALIDEE');
    $em->flush();

    $this->addFlash('success', 'Transfert créé : stock CNTS décrémenté et stock Banque mis à jour.');

    return $this->redirectToRoute('back_transfert_index');
}



    #[Route('/refuser/{id}', name: 'back_transfert_refuser')]
    public function refuser(Demande $demande, EntityManagerInterface $em): Response
    {
        $demande->setStatus('REFUSEE');
        $em->flush();

        return $this->redirectToRoute('back_transfert_index');
    }

    #[Route('/delete/{id}', name: 'back_transfert_delete', methods: ['POST'])]
    public function delete(Request $request, Transfert $transfert, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transfert->getId(), $request->request->get('_token'))) {
            $em->remove($transfert);
            $em->flush();
        }

        return $this->redirectToRoute('back_transfert_index');
    }
    #[Route('/confirmer/{id}', name: 'back_transfert_confirmer', methods: ['GET'])]
public function confirmer(Transfert $transfert, EntityManagerInterface $em): Response
{
    $transfert->setStatus('CONFIRME');
    $transfert->setDateReception(new \DateTime());

    $em->flush();

    $this->addFlash('success', 'Transfert confirmé avec succès.');

    return $this->redirectToRoute('front_transfert_index');
}
#[Route('/{id}/edit', name: 'back_transfert_edit', methods: ['GET','POST'])]
public function edit(Request $request, Transfert $transfert, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(TransfertType::class, $transfert);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Mettre updatedAt à maintenant
        $transfert->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        $this->addFlash('success', 'Transfert modifié avec succès !');
        return $this->redirectToRoute('back_transfert_index');
    }

    return $this->render('transfert/edit.html.twig', [
        'transfert' => $transfert,
        'form' => $form,
    ]);
}
}
