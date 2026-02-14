<?php

namespace App\Entity;

use App\Repository\TransfertRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransfertRepository::class)]
class Transfert
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transferts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Demande $demande = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le type d'organisation (From) est obligatoire.")]
    #[Assert\Positive(message: "ID FromOrg invalide.")]
    private ?int $fromOrgId = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'organisation (From) est obligatoire.")]
    private ?string $fromOrg = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "L'ID de l'organisation (To) est obligatoire.")]
    #[Assert\Positive(message: "ID ToOrg invalide.")]
    private ?int $toOrgId = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'organisation (To) est obligatoire.")]
    private ?string $toOrg = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date d'envoi est obligatoire.")]
    private ?\DateTime $dateEnvoie = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de réception est obligatoire.")]
    #[Assert\GreaterThanOrEqual(
        propertyPath: "dateEnvoie",
        message: "La date de réception doit être égale ou postérieure à la date d'envoi."
    )]
    private ?\DateTime $dateReception = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La quantité est obligatoire.")]
    #[Assert\Positive(message: "La quantité doit être supérieure à 0.")]
    private ?int $quantite = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'transferts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le stock est obligatoire.")]
    private ?Stock $stock = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDemande(): ?Demande
    {
        return $this->demande;
    }

    public function setDemande(?Demande $demande): static
    {
        $this->demande = $demande;
        return $this;
    }

    public function getFromOrgId(): ?int
    {
        return $this->fromOrgId;
    }

    public function setFromOrgId(int $fromOrgId): static
    {
        $this->fromOrgId = $fromOrgId;
        return $this;
    }

    public function getFromOrg(): ?string
    {
        return $this->fromOrg;
    }

    public function setFromOrg(string $fromOrg): static
    {
        $this->fromOrg = $fromOrg;
        return $this;
    }

    public function getToOrgId(): ?int
    {
        return $this->toOrgId;
    }

    public function setToOrgId(int $toOrgId): static
    {
        $this->toOrgId = $toOrgId;
        return $this;
    }

    public function getToOrg(): ?string
    {
        return $this->toOrg;
    }

    public function setToOrg(string $toOrg): static
    {
        $this->toOrg = $toOrg;
        return $this;
    }

    public function getDateEnvoie(): ?\DateTime
    {
        return $this->dateEnvoie;
    }

    public function setDateEnvoie(\DateTime $dateEnvoie): static
    {
        $this->dateEnvoie = $dateEnvoie;
        return $this;
    }

    public function getDateReception(): ?\DateTime
    {
        return $this->dateReception;
    }

    public function setDateReception(\DateTime $dateReception): static
    {
        $this->dateReception = $dateReception;
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getStock(): ?Stock
    {
        return $this->stock;
    }

    public function setStock(?Stock $stock): static
    {
        $this->stock = $stock;
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
}
