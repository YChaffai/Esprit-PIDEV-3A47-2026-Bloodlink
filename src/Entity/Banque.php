<?php

namespace App\Entity;

use App\Repository\BanqueRepository;
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
  #[Assert\NotBlank(message: 'bank name cannot be empty')]
  #[Assert\Length(
    min: 3,
    max: 255,
    minMessage: 'bank name must be at least {{ limit }} characters',
    maxMessage: 'bank name cannot be longer than {{ limit }} characters'
  )]
  private ?string $nom = null;

  #[ORM\Column(length: 255)]
  #[Assert\NotBlank(message: 'address cannot be empty')]
  #[Assert\Length(
    min: 5,
    max: 255,
    minMessage: 'address must be at least {{ limit }} characters',
    maxMessage: 'address cannot be longer than {{ limit }} characters'
  )]
  private ?string $adresse = null;

  #[ORM\Column(length: 255)]
  #[Assert\NotBlank(message: 'phone number cannot be empty')]
  #[Assert\Regex(
    pattern: '/^\d{8}$/',
    message: 'phone number must be exactly 8 digits'
  )]
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
}
