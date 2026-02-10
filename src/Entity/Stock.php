<?php

namespace App\Entity;

use App\Repository\StockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: StockRepository::class)]
class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'L\'organisation est obligatoire.')]
    #[Assert\Positive(message: 'L\'organisation doit être valide.')]
    private ?int $type_orgid = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le type d\'organisation est obligatoire.')]
    #[Assert\Choice(
        choices: ['banque', 'entitecollecte', 'crts', 'cnts'],
        message: 'Type d\'organisation invalide.'
    )]
    private ?string $type_org = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le type de sang est obligatoire.')]
    #[Assert\Choice(
        choices: ['A+','A-','B+','B-','AB+','AB-','O+','O-'],
        message: 'Type de sang invalide.'
    )]
    private ?string $type_sang = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantité est obligatoire.')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'La quantité ne peut pas être négative.')]
    private ?int $quantite = null;


    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updated_at = null;

    /**
     * @var Collection<int, Commande>
     */
    #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'stock')]
    private Collection $commandes;

    public function __construct()
    {
        $this->commandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTypeOrgid(): ?int
    {
        return $this->type_orgid;
    }

    public function setTypeOrgid(?int $type_orgid): static
    {
        $this->type_orgid = $type_orgid;

        return $this;
    }

    public function getTypeOrg(): ?string
    {
        return $this->type_org;
    }

    public function setTypeOrg(?string $type_org): static
    {
        $this->type_org = $type_org;

        return $this;
    }

    public function getTypeSang(): ?string
    {
        return $this->type_sang;
    }

    public function setTypeSang(?string $type_sang): static
    {
        $this->type_sang = $type_sang;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, Commande>
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): static
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes->add($commande);
            $commande->setStock($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande): static
    {
        if ($this->commandes->removeElement($commande)) {
            // set the owning side to null (unless already changed)
            if ($commande->getStock() === $this) {
                $commande->setStock(null);
            }
        }

        return $this;
    }
    public function __toString(): string
    {
        return $this->getTypeSang().' - '.$this->getTypeOrg().' ('.$this->getQuantite().')';
    }
}
