<?php

namespace App\Controller\Back;

use App\Entity\Stock;
use App\Form\StockType;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/stock')]
class StockController extends AbstractController
{
    #[Route('', name: 'back_stock_index', methods: ['GET'])]
    public function index(Request $request, StockRepository $stockRepository): Response
    {
        $search = $request->query->get('search', '');
        $typeOrg = $request->query->get('type_org', '');
        $typeSang = $request->query->get('type_sang', '');

        $criteria = [
            'search' => $search,
            'type_org' => $typeOrg,
            'type_sang' => $typeSang,
        ];

        $stocks = $stockRepository->searchBy($criteria);

        if ($request->isXmlHttpRequest()) {
            return $this->render('back/stock/_stock_table.html.twig', [
                'stocks' => $stocks,
            ]);
        }

        return $this->render('back/Stock.html.twig', [
            'stocks' => $stocks,
            'search' => $search,
            'type_org' => $typeOrg,
            'type_sang' => $typeSang,
        ]);
    }

    #[Route('/new', name: 'back_stock_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $stock = new Stock();
        $stock->setCreatedAt(new \DateTimeImmutable());

        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($stock);
            $em->flush();

            $this->addFlash('success', 'Stock créé avec succès.');
            return $this->redirectToRoute('back_stock_index');
        }

        return $this->render('back/AjoutStock.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'back_stock_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Stock $stock, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(StockType::class, $stock);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $stock->setUpdatedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Stock mis à jour avec succès.');
            return $this->redirectToRoute('back_stock_index');
        }

        return $this->render('back/editStock.html.twig', [
            'stock' => $stock,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'back_stock_delete', methods: ['POST'])]
    public function delete(Request $request, Stock $stock, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $stock->getId(), $request->request->get('_token'))) {
            $em->remove($stock);
            $em->flush();
            $this->addFlash('success', 'Stock supprimé avec succès.');
        }

        return $this->redirectToRoute('back_stock_index');
    }
}
