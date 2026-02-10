<?php

namespace App\Entity;

use App\Repository\CampagneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampagneRepository::class)]
class Campagne
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $titre = null;

  /**
   * @var Collection<int, Questionnaire>
   */
  #[ORM\OneToMany(targetEntity: Questionnaire::class, mappedBy: 'campagne', orphanRemoval: true)]
  private Collection $questionnaires;

  /**
   * @var Collection<int, EntiteCollecte>
   */
  #[ORM\ManyToMany(targetEntity: EntiteCollecte::class, inversedBy: 'campagnes')]
  private Collection $entities;

  #[ORM\Column(type: Types::DATE_MUTABLE)]
  private ?\DateTimeInterface $date_fin = null;

  #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
  private ?\DateTime $date_debut = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $type_sang = null;


  public function __construct()
  {
    $this->questionnaires = new ArrayCollection();
    $this->entities = new ArrayCollection();
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getTitre(): ?string
  {
    return $this->titre;
  }

  public function setTitre(string $titre): static
  {
    $this->titre = $titre;

    return $this;
  }

  /**
   * @return Collection<int, Questionnaire>
   */
  public function getQuestionnaires(): Collection
  {
    return $this->questionnaires;
  }

  public function addQuestionnaire(Questionnaire $questionnaire): static
  {
    if (!$this->questionnaires->contains($questionnaire)) {
      $this->questionnaires->add($questionnaire);
      $questionnaire->setCampagne($this);
    }

    return $this;
  }

  public function removeQuestionnaire(Questionnaire $questionnaire): static
  {
    if ($this->questionnaires->removeElement($questionnaire)) {
      // set the owning side to null (unless already changed)
      if ($questionnaire->getCampagne() === $this) {
        $questionnaire->setCampagne(null);
      }
    }

    return $this;
  }

  /**
   * @return Collection<int, EntiteCollecte>
   */
  public function getEntities(): Collection
  {
    return $this->entities;
  }

  public function addEntity(EntiteCollecte $entity): static
  {
    if (!$this->entities->contains($entity)) {
      $this->entities->add($entity);
    }

    return $this;
  }

  public function removeEntity(EntiteCollecte $entity): static
  {
    $this->entities->removeElement($entity);

    return $this;
  }

  public function getDateFin(): ?\DateTimeInterface
  {
    return $this->date_fin;
  }

  public function setDateFin(\DateTimeInterface $date_fin): static
  {
    $this->date_fin = $date_fin;

    return $this;
  }

  public function getDateDebut(): ?\DateTime
  {
    return $this->date_debut;
  }

  public function setDateDebut(?\DateTime $date_debut): static
  {
    $this->date_debut = $date_debut;

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
}
