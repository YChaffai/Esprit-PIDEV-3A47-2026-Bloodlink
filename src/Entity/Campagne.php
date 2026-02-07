<?php

namespace App\Entity;

use App\Repository\CampagneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column]
    private ?\DateTime $date_fin = null;

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

    public function getDateFin(): ?\DateTime
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTime $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    
}
