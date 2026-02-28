<?php

namespace App\Service;

use App\Entity\Commande;
use App\Repository\StockRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockAlloc
{
    public function __construct(
        private StockRepository $stockRepo,
        private EntityManagerInterface $em
    ) {}

    public function assignStockOrThrow(Commande $commande): void
    {
        $banque = $commande->getBanque();
        if (!$banque || !$banque->getId()) {
            throw new \RuntimeException("Veuillez choisir une banque.");
        }

        $typeSang = $commande->getTypeSang();
        $qty = (int) $commande->getQuantite();

        if ($qty <= 0) {
            throw new \RuntimeException("La quantité doit être > 0.");
        }

        // Find stock for this banque + blood type using consistent ids/casing
        $stock = $this->stockRepo->findAvailableForBanque($banque, $typeSang, $qty);

        if (!$stock) {
            throw new \RuntimeException("Aucun stock disponible pour ce type de sang dans cette banque.");
        }

        if ($stock->getQuantite() < $qty) {
            throw new \RuntimeException("Stock insuffisant. Disponible: ".$stock->getQuantite().", demandé: ".$qty);
        }

        // ✅ assign stock + decrease quantity
        $commande->setStock($stock);
        $stock->setQuantite($stock->getQuantite() - $qty);

        // optional timestamps (if you manage them manually)

        // no flush here, controller will flush
        $this->em->persist($stock);
    }
}
