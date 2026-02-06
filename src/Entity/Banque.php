<?php

namespace App\Entity;

use App\Repository\BanqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BanqueRepository::class)]
class Banque
{
  #[ORM\Id]
  #[ORM\OneToOne(targetEntity: User::class, inversedBy: "banque")]
  #[ORM\JoinColumn(name: "id", referencedColumnName: "id", onDelete: "CASCADE")]
  private ?User $user = null;

  #[ORM\Column(length: 255)]
  private ?string $nom = null;

  #[ORM\Column(length: 255)]
  private ?string $adresse = null;

  #[ORM\Column(length: 255)]
  private ?string $telephone = null;

  public function getUser(): ?User
  {
    return $this->user;
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

  public function setNom(string $nom): static
  {
    $this->nom = $nom;

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

  public function getTelephone(): ?string
  {
    return $this->telephone;
  }

  public function setTelephone(string $telephone): static
  {
    $this->telephone = $telephone;

    return $this;
  }
}
