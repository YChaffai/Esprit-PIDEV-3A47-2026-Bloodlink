<?php

namespace App\Entity;

use App\Repository\EntitecollecteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntitecollecteRepository::class)]
class Entitecollecte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $localisation = null;

    #[ORM\Column]
    private ?int $telephone = null;

    #[ORM\OneToMany(mappedBy: 'entite', targetEntity: Compagne::class)]
    private Collection $campagnes;

    public function __construct()
    {
        $this->campagnes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;
        return $this;
    }

    public function getTelephone(): ?int
    {
        return $this->telephone;
    }

    public function setTelephone(int $telephone): static
    {
        $this->telephone = $telephone;
        return $this;
    }

    /**
     * @return Collection|Compagne[]
     */
    public function getCampagnes(): Collection
    {
        return $this->campagnes;
    }

    public function addCampagne(Compagne $compagne): self
    {
        if (!$this->campagnes->contains($compagne)) {
            $this->campagnes[] = $compagne;
            $compagne->setEntite($this);
        }

        return $this;
    }

    public function removeCampagne(Compagne $compagne): self
    {
        if ($this->campagnes->removeElement($compagne)) {
            if ($compagne->getEntite() === $this) {
                $compagne->setEntite(null);
            }
        }

        return $this;
    }
}
