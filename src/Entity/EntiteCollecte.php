<?php

namespace App\Entity;

use App\Repository\EntiteCollecteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EntiteCollecteRepository::class)]
class EntiteCollecte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $nom = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le téléphone est obligatoire.')]
    private ?string $telephone = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Le type est obligatoire.')]
    private ?string $type = 'Hôpital'; // Hôpital, Banque, Point Mobile

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse est obligatoire.')]
    private ?string $adresse = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'La ville est obligatoire.')]
    private ?string $ville = null;

    /**
     * @var Collection<int, Compagne>
     */
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


    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    /**
     * @return Collection<int, Compagne>
     */
    public function getCampagnes(): Collection
    {
        return $this->campagnes;
    }

    public function addCampagne(Compagne $campagne): static
    {
        if (!$this->campagnes->contains($campagne)) {
            $this->campagnes->add($campagne);
            $campagne->addEntite($this);
        }

        return $this;
    }

    public function removeCampagne(Compagne $campagne): static
    {
        if ($this->campagnes->removeElement($campagne)) {
            $campagne->removeEntite($this);
        }

        return $this;
    }
}
