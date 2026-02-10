<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

  /**
   * @var Collection<int, Commande>
   */
  #[ORM\OneToMany(targetEntity: Commande::class, mappedBy: 'client')]
  private Collection $Commandes;

  public function __construct()
  {
      $this->Commandes = new ArrayCollection();
  }

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

  /**
   * @return Collection<int, Commande>
   */
  public function getCommandes(): Collection
  {
      return $this->Commandes;
  }

  public function addCommande(Commande $commande): static
  {
      if (!$this->Commandes->contains($commande)) {
          $this->Commandes->add($commande);
          $commande->setClient($this);
      }

      return $this;
  }

  public function removeCommande(Commande $commande): static
  {
      if ($this->Commandes->removeElement($commande)) {
          // set the owning side to null (unless already changed)
          if ($commande->getClient() === $this) {
              $commande->setClient(null);
          }
      }

      return $this;
  }
  public function __toString(): string
    {
        return (string) $this->user;
    }
}