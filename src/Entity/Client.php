<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
  #[ORM\Id]
  #[ORM\OneToOne(targetEntity: User::class, inversedBy: "client")]
  #[ORM\JoinColumn(name: "id", referencedColumnName: "id", onDelete: "CASCADE")]
  private ?User $user = null;

  #[ORM\Column(length: 255)]
  private ?string $typeSang = null;

  #[ORM\Column(type: Types::DATE_MUTABLE)]
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

  public function setTypeSang(string $typeSang): static
  {
    $this->typeSang = $typeSang;

    return $this;
  }

  public function getDernierDon(): ?\DateTime
  {
    return $this->dernierDon;
  }

  public function setDernierDon(\DateTime $dernierDon): static
  {
    $this->dernierDon = $dernierDon;

    return $this;
  }
}
