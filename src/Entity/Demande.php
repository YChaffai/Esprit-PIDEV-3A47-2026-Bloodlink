<?php

namespace App\Entity;

use App\Entity\Banque;
use App\Entity\User;
use App\Repository\DemandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $client = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(name: 'id_banque', nullable: false)]
    private ?Banque $banque = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(message: "Le type de sang est obligatoire.")]
    #[Assert\Choice(
        choices: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
        message: "Type de sang invalide."
    )]
    private ?string $typeSang = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La quantité est obligatoire.")]
    #[Assert\Positive(message: "La quantité doit être supérieure à 0.")]
    #[Assert\LessThanOrEqual(
        value: 500,
        message: "La quantité maximale autorisée est 500."
    )]
    private ?int $quantite = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le niveau d'urgence est obligatoire.")]
    #[Assert\Choice(
        choices: ['Normale', 'Urgente'],
        message: "Niveau d'urgence invalide."
    )]
    private ?string $urgence = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(
        choices: ['EN_ATTENTE', 'EN_COURS', 'SATISFAITE', 'ANNULEE', 'VALIDEE', 'REFUSEE'],
        message: "Status invalide."
    )]
    private ?string $status = 'EN_ATTENTE';

    #[ORM\Column]
    #[Assert\NotNull]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Transfert::class, mappedBy: 'demande')]
    private Collection $transferts;

    public function __construct()
    {
        $this->transferts = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClient(): ?User
    {
        return $this->client;
    }

    public function setClient(?User $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getBanque(): ?Banque
    {
        return $this->banque;
    }

    public function setBanque(?Banque $banque): static
    {
        $this->banque = $banque;
        return $this;
    }

    public function getIdBanque(): ?int
    {
        return $this->banque?->getId();
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

    public function getUrgence(): ?string
    {
        return $this->urgence;
    }

    public function setUrgence(string $urgence): static
    {
        $this->urgence = $urgence;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getTransferts(): Collection
    {
        return $this->transferts;
    }

    public function addTransfert(Transfert $transfert): static
    {
        if (!$this->transferts->contains($transfert)) {
            $this->transferts->add($transfert);
            $transfert->setDemande($this);
        }

        return $this;
    }

    public function removeTransfert(Transfert $transfert): static
    {
        if ($this->transferts->removeElement($transfert)) {
            if ($transfert->getDemande() === $this) {
                $transfert->setDemande(null);
            }
        }

        return $this;
    }
}
