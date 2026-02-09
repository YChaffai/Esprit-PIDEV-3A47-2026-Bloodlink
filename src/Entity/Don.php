<?php

namespace App\Entity;

use App\Repository\DonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DonRepository::class)]
#[ORM\HasLifecycleCallbacks] // ✅ Required for automatic timestamps
class Don
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "La date est obligatoire.")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La quantité est obligatoire.")]
    #[Assert\Positive(message: "La quantité doit être positive.")]
    #[Assert\Range(
        min: 50,
        max: 1000,
        notInRangeMessage: "Quantité invalide (50–1000 ml)."
    )]
    private ?float $quantite = null;

    #[ORM\Column(name: 'type_don', length: 255)]
    #[Assert\NotBlank(message: "Le type de don est obligatoire.")]
    #[Assert\Choice(
        choices: ["Sang total", "Plasma", "Plaquettes", "Globules rouges"],
        message: "Type de don invalide."
    )]
    private ?string $typeDon = null;

    // ✅ Matches 'id_entite' requirement
    #[ORM\Column(name: 'id_entite')]
    #[Assert\NotNull(message: "L'entité est obligatoire.")]
    private ?int $idEntite = 1;

    // ✅ Matches 'id_client' requirement (Foreign Key)
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: "Le client est obligatoire.")]
    private ?Client $client = null;

    // ✅ NEW: Created At
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    // ✅ NEW: Updated At
    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    // --- LIFECYCLE CALLBACKS ---

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        // Also set updatedAt on creation
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    // --- GETTERS & SETTERS ---

    public function getId(): ?int { return $this->id; }

    public function getDate(): ?\DateTimeInterface { return $this->date; }
    public function setDate(\DateTimeInterface $date): static { $this->date = $date; return $this; }

    public function getQuantite(): ?float { return $this->quantite; }
    public function setQuantite(float $quantite): static { $this->quantite = $quantite; return $this; }

    public function getTypeDon(): ?string { return $this->typeDon; }
    public function setTypeDon(string $typeDon): static { $this->typeDon = $typeDon; return $this; }

    public function getIdEntite(): ?int { return $this->idEntite; }
    public function setIdEntite(int $idEntite): static { $this->idEntite = $idEntite; return $this; }

    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): static { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt(): ?\DateTimeInterface { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static { $this->updatedAt = $updatedAt; return $this; }

    public function __toString(): string
    {
        return sprintf('#%d - %s (%s)', $this->id, $this->typeDon, $this->date ? $this->date->format('d/m/Y') : 'N/A');
    }
}