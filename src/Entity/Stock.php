<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Transfert;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $typeOrgId = null;

    #[ORM\Column(length: 50)]
    private ?string $typeOrg = null;

    #[ORM\Column(length: 50)]
    private ?string $typeSang = null;

    #[ORM\Column]
    private ?int $quantite = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    // 🔹 Relation avec Transfert
    #[ORM\OneToMany(mappedBy: 'stock', targetEntity: Transfert::class)]
    private Collection $transferts;

    public function __construct()
    {
        $this->transferts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeOrgId(): ?int
    {
        return $this->typeOrgId;
    }

    public function setTypeOrgId(int $typeOrgId): static
    {
        $this->typeOrgId = $typeOrgId;
        return $this;
    }

    public function getTypeOrg(): ?string
    {
        return $this->typeOrg;
    }

    public function setTypeOrg(string $typeOrg): static
    {
        $this->typeOrg = $typeOrg;
        return $this;
    }

    public function getTypeSang(): ?string
    {
        return $this->typeSang;
    }

    public function setTypeSang(string $typeSang): static
    {
        $this->typeSang = $typeSang;
        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection|Transfert[]
     */
    public function getTransferts(): Collection
    {
        return $this->transferts;
    }

    public function addTransfert(Transfert $transfert): static
    {
        if (!$this->transferts->contains($transfert)) {
            $this->transferts[] = $transfert;
            $transfert->setStock($this);
        }
        return $this;
    }

    public function removeTransfert(Transfert $transfert): static
    {
        if ($this->transferts->removeElement($transfert)) {
            // set the owning side to null (unless already changed)
            if ($transfert->getStock() === $this) {
                $transfert->setStock(null);
            }
        }
        return $this;
    }
}
