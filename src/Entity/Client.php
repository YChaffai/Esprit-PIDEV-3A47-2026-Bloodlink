<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\OneToOne(inversedBy: 'client', cascade: ['persist', 'remove'])]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $user = null;

  #[ORM\Column(length: 255)]
  #[Assert\NotBlank(message: 'Le groupe sanguin doit être sélectionné')]
  #[Assert\Choice(
    choices: ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'],
    message: 'Veuillez sélectionner un groupe sanguin valide'
  )]
  private ?string $typeSang = null;

  #[ORM\Column(type: Types::DATE_MUTABLE)]
  #[Assert\NotBlank(message: 'La date du dernier don ne peut pas être vide')]
  #[Assert\LessThanOrEqual(
    value: 'today',
    message: 'La date du dernier don ne peut pas être dans le futur'
  )]
  private ?\DateTimeInterface $dernierDon = null;

  #[ORM\OneToMany(mappedBy: 'client', targetEntity: Commande::class)]
  private Collection $commandes;

  #[ORM\OneToMany(mappedBy: 'client', targetEntity: Questionnaire::class)]
  private Collection $questionnaires;

  public function __construct()
  {
    $this->commandes = new ArrayCollection();
    $this->questionnaires = new ArrayCollection();
  }

  public function __toString(): string
  {
    return $this->user ? $this->user->getEmail() : 'Client sans utilisateur';
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function setUser(?User $user): static
  {
    $this->user = $user;
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
          $commande->setClient($this);
      }

      return $this;
  }

  public function removeCommande(Commande $commande): static
  {
      if ($this->commandes->removeElement($commande)) {
          // set the owning side to null (unless already changed)
          if ($commande->getClient() === $this) {
              $commande->setClient(null);
          }
      }

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
          $questionnaire->setClient($this);
      }

      return $this;
  }

  public function removeQuestionnaire(Questionnaire $questionnaire): static
  {
      if ($this->questionnaires->removeElement($questionnaire)) {
          // set the owning side to null (unless already changed)
          if ($questionnaire->getClient() === $this) {
              $questionnaire->setClient(null);
          }
      }

      return $this;
  }
}
