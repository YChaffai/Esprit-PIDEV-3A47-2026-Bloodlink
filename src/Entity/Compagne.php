<?php

namespace App\Entity;

use App\Repository\CompagneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;




#[ORM\Entity(repositoryClass: CompagneRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Compagne
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Entitecollecte::class, inversedBy: 'campagnes')]
    #[ORM\JoinColumn(name: "id_entite", referencedColumnName: "id", nullable: true, onDelete: "SET NULL")]
    private ?Entitecollecte $entite = null;


    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date_fin = null;

    #[ORM\Column]
    private ?\DateTime $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $updated_at = null;


    
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created_at = new \DateTime();
    }
    
    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updated_at = new \DateTime();
    }
    

    // --- Getters & Setters ---
    public function getId(): ?int { return $this->id; }

    public function getEntite(): ?Entitecollecte { return $this->entite; }
    public function setEntite(?Entitecollecte $entite): self { $this->entite = $entite; return $this; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(string $titre): self { $this->titre = $titre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getDateDebut(): ?\DateTime { return $this->date_debut; }
    public function setDateDebut(\DateTime $date_debut): self { $this->date_debut = $date_debut; return $this; }

    public function getDateFin(): ?\DateTime { return $this->date_fin; }
    public function setDateFin(\DateTime $date_fin): self { $this->date_fin = $date_fin; return $this; }

    public function getCreatedAt(): ?\DateTime { return $this->created_at; }
    public function setCreatedAt(\DateTime $created_at): self { $this->created_at = $created_at; return $this; }

    public function getUpdatedAt(): ?\DateTime { return $this->updated_at; }
    public function setUpdatedAt(?\DateTime $updated_at): self { $this->updated_at = $updated_at; return $this; }
}
