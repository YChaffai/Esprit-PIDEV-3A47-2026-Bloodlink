<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
  #[ORM\Id]
  #[ORM\OneToOne(targetEntity: User::class, inversedBy: "client")]
  #[ORM\JoinColumn(name: "id", referencedColumnName: "id", onDelete: "CASCADE")]
  private ?User $user = null;

  #[ORM\Column(length: 255)]
  #[Assert\NotBlank(message: 'blood type must be selected')]
  #[Assert\Choice(
    choices: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
    message: 'Please select a valid blood type'
  )]
  private ?string $typeSang = null;

  #[ORM\Column(type: Types::DATE_MUTABLE)]
  #[Assert\NotBlank(message: 'last donation date cannot be empty')]
  #[Assert\LessThanOrEqual(
    value: 'today',
    message: 'last donation date cannot be in the future'
  )]
  private ?\DateTime $dernierDon = null;

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function setUser(User $user): static
  {
    $this->user = $user;
    if ($user->getClient() !== $this) {
      $user->setClient($this);
    }
    return $this;
  }

  public function getTypeSang(): ?string
  {
    return $this->typeSang;
  }

  public function setTypeSang(?string $typeSang): static
  {
    $this->typeSang = $typeSang;
    return $this;
  }

  public function getDernierDon(): ?\DateTime
  {
    return $this->dernierDon;
  }

  public function setDernierDon(?\DateTime $dernierDon): static
  {
    $this->dernierDon = $dernierDon;
    return $this;
  }
}
