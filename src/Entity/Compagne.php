<?php

namespace App\Entity;

use App\Repository\CompagneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Validator\UniqueCampagneTypeSang;

#[ORM\Entity(repositoryClass: CompagneRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['titre'], message: 'Une campagne avec ce titre existe déjà.')]
#[UniqueCampagneTypeSang]
class Compagne{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToMany(targetEntity: EntiteCollecte::class, inversedBy: 'campagnes')]
    #[Assert\Count(min: 1, minMessage: "Veuillez sélectionner au moins une entité")]
    private Collection $entites;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Assert\NotBlank(message: "Veuillez sélectionner au moins un type de sang")]
    #[Assert\Count(min: 1, minMessage: "Veuillez sélectionner au moins un type de sang")]
    private ?array $typeSang = [];

    #[ORM\OneToMany(mappedBy: 'campagne', targetEntity: Questionnaire::class)]
    private Collection $questionnaires;

    public function __construct()
    {
        $this->entites = new ArrayCollection();
        $this->questionnaires = new ArrayCollection();
    }


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le titre est obligatoire")]
    #[Assert\Length(max: 255)]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de début est obligatoire")]
    private ?\DateTime $date_debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "La date de fin est obligatoire")]
    #[Assert\GreaterThan(propertyPath: "date_debut", message: "La date de fin doit être postérieure à la date de début")]
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

    /**
     * @return Collection<int, EntiteCollecte>
     */
    public function getEntites(): Collection
    {
        return $this->entites;
    }

    public function addEntite(EntiteCollecte $entite): self
    {
        if (!$this->entites->contains($entite)) {
            $this->entites->add($entite);
        }

        return $this;
    }

    public function removeEntite(EntiteCollecte $entite): self
    {
        $this->entites->removeElement($entite);

        return $this;
    }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(?string $titre): self { $this->titre = $titre; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }

    public function getDateDebut(): ?\DateTime { return $this->date_debut; }
    public function setDateDebut(?\DateTime $date_debut): self { $this->date_debut = $date_debut; return $this; }

    public function getDateFin(): ?\DateTime { return $this->date_fin; }
    public function setDateFin(?\DateTime $date_fin): self { $this->date_fin = $date_fin; return $this; }

    public function getCreatedAt(): ?\DateTime { return $this->created_at; }
    public function setCreatedAt(\DateTime $created_at): self { $this->created_at = $created_at; return $this; }

    public function getUpdatedAt(): ?\DateTime { return $this->updated_at; }
    public function setUpdatedAt(?\DateTime $updated_at): self { $this->updated_at = $updated_at; return $this; }

    public function getTypeSang(): array { return $this->typeSang ?? []; }
    public function setTypeSang(?array $typeSang): self { $this->typeSang = $typeSang ?? []; return $this; }

    /**
     * @return Collection<int, Questionnaire>
     */
    public function getQuestionnaires(): Collection
    {
        return $this->questionnaires;
    }

    public function addQuestionnaire(Questionnaire $questionnaire): self
    {
        if (!$this->questionnaires->contains($questionnaire)) {
            $this->questionnaires->add($questionnaire);
            $questionnaire->setCampagne($this);
        }

        return $this;
    }

    public function removeQuestionnaire(Questionnaire $questionnaire): self
    {
        if ($this->questionnaires->removeElement($questionnaire)) {
            // set the owning side to null (unless already changed)
            if ($questionnaire->getCampagne() === $this) {
                $questionnaire->setCampagne(null);
            }
        }

        return $this;
    }
}
