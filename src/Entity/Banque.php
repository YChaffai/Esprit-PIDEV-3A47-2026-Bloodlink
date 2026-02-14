<?php

namespace App\Entity;

use App\Repository\BanqueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BanqueRepository::class)]
class Banque
{
  #[ORM\Id]
  #[ORM\OneToOne(targetEntity: User::class, inversedBy: "banque")]
  #[ORM\JoinColumn(name: "id", referencedColumnName: "id", onDelete: "CASCADE")]
  private ?User $user = null;

  #[ORM\Column(length: 255)]
  #[Assert\NotBlank(message: 'Le nom de la banque ne peut pas être vide')]
  #[Assert\Length(
    min: 3,
    max: 255,
    minMessage: 'Le nom de la banque doit comporter au moins {{ limit }} caractères',
    maxMessage: 'Le nom de la banque ne peut pas dépasser {{ limit }} caractères'
  )]
  private ?string $nom = null;

  #[ORM\Column(length: 255)]
  #[Assert\NotBlank(message: "L'adresse ne peut pas être vide")]
  #[Assert\Length(
    min: 5,
    max: 255,
    minMessage: "L'adresse doit comporter au moins {{ limit }} caractères",
    maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères"
  )]
  private ?string $adresse = null;

  #[ORM\Column(length: 255)]
  #[Assert\NotBlank(message: 'Le numéro de téléphone ne peut pas être vide')]
  #[Assert\Regex(
    pattern: '/^\d{8}$/',
    message: 'Le numéro de téléphone doit comporter exactement 8 chiffres'
  )]
  private ?string $telephone = null;

  #[ORM\OneToMany(mappedBy: 'banque', targetEntity: Commande::class)]
  private Collection $commandes;

  public function __construct()
  {
      $this->commandes = new ArrayCollection();
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function getId(): ?int
  {
      return $this->user?->getId();
  }

  public function setUser(User $user): static
  {
    $this->user = $user;
    if ($user->getBanque() !== $this) {
      $user->setBanque($this);
    }
    return $this;
  }

  public function getNom(): ?string
  {
    return $this->nom;
  }

  public function setNom(?string $nom): static
  {
    $this->nom = $nom;
    return $this;
  }

  public function getAdresse(): ?string
  {
    return $this->adresse;
  }

  public function setAdresse(?string $adresse): static
  {
    $this->adresse = $adresse;
    return $this;
  }

  public function getTelephone(): ?string
  {
    return $this->telephone;
  }

  public function setTelephone(?string $telephone): static
  {
    $this->telephone = $telephone;
    return $this;
  }

  /**
   * @return Collection<int, Commande>
   */
  public function getCommandes(): Collection
  {
      return $this->commandes;
  }

  public function addCommande(Commande $commande): static
  {
      if (!$this->commandes->contains($commande)) {
          $this->commandes->add($commande);
          $commande->setBanque($this);
      }

      return $this;
  }

  public function removeCommande(Commande $commande): static
  {
      if ($this->commandes->removeElement($commande)) {
          // set the owning side to null (unless already changed)
          if ($commande->getBanque() === $this) {
              $commande->setBanque(null);
          }
      }

      return $this;
  }
}
