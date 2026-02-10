<?php

namespace App\Entity;

use App\Repository\EntitecollecteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EntitecollecteRepository::class)]
class Entitecollecte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom de l'entité est obligatoire")]
    #[Assert\Length(max: 255, maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères")]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La localisation est obligatoire")]
    #[Assert\Length(max: 255, maxMessage: "La localisation ne peut pas dépasser {{ limit }} caractères")]
    private ?string $localisation = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Le numéro de téléphone est obligatoire")]
    #[Assert\Length(
        min: 8,
        max: 20,
        minMessage: "Le numéro de téléphone doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[0-9]+$/",
        message: "Le numéro de téléphone ne doit contenir que des chiffres"
    )]
    private ?string $telephone = null;

    #[ORM\ManyToMany(targetEntity: Compagne::class, mappedBy: 'entites')]
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
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
            $this->campagnes->add($compagne);
            $compagne->addEntite($this);
        }

        return $this;
    }

    public function removeCampagne(Compagne $compagne): self
    {
        if ($this->campagnes->removeElement($compagne)) {
            $compagne->removeEntite($this);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom ?? '';
    }
}
