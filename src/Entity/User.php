<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  #[Assert\NotBlank(message: 'Le nom ne peut pas être vide')]
  #[Assert\Length(
    min: 2,
    max: 255,
    minMessage: 'Le nom doit comporter au moins {{ limit }} caractères',
    maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères'
  )]
  private ?string $nom = null;

  #[ORM\Column(length: 255)]
  #[Assert\NotBlank(message: 'Le prénom ne peut pas être vide')]
  #[Assert\Length(
    min: 2,
    max: 255,
    minMessage: 'Le prénom doit comporter au moins {{ limit }} caractères',
    maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères'
  )]
  private ?string $prenom = null;

  #[ORM\Column(length: 255, unique: true)]
  #[Assert\NotBlank(message: "L'adresse email ne peut pas être vide")]
  #[Assert\Email(message: "L'adresse email '{{ value }}' n'est pas valide")]
  private ?string $email = null;

  #[ORM\Column(length: 255)]
  private ?string $password = null;

  #[ORM\Column(length: 255)]
  #[Assert\NotBlank(message: 'Le rôle doit être spécifié')]
  #[Assert\Choice(
    choices: ['admin', 'client', 'doctor', 'banque', 'cnts'],
    message: 'Veuillez sélectionner un rôle valide'
  )]
  private ?string $role = null;

  #[ORM\OneToOne(mappedBy: "user", targetEntity: Client::class, cascade: ["persist", "remove"])]
  private ?Client $client = null;

  #[ORM\OneToOne(mappedBy: "user", targetEntity: Banque::class, cascade: ["persist", "remove"])]
  private ?Banque $banque = null;

  public function getClient(): ?Client
  {
    return $this->client;
  }

  public function setClient(?Client $client): static
  {
    $this->client = $client;
    if ($client && $client->getUser() !== $this) {
      $client->setUser($this);
    }
    return $this;
  }

  public function getBanque(): ?Banque
  {
    return $this->banque;
  }

  public function setBanque(?Banque $banque): static
  {
    $this->banque = $banque;
    if ($banque && $banque->getUser() !== $this) {
      $this->banque = $banque;
    }
    return $this;
  }

  public function getId(): ?int
  {
    return $this->id;
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

  public function getPrenom(): ?string
  {
    return $this->prenom;
  }

  public function setPrenom(?string $prenom): static
  {
    $this->prenom = $prenom;
    return $this;
  }

  public function getEmail(): ?string
  {
    return $this->email;
  }

  public function setEmail(?string $email): static
  {
    $this->email = $email;
    return $this;
  }

  public function getPassword(): ?string
  {
    return $this->password;
  }

  public function setPassword(?string $password): static
  {
    $this->password = $password;
    return $this;
  }

  public function getRole(): ?string
  {
    return $this->role;
  }

  public function setRole(?string $role): static
  {
    $this->role = $role;
    return $this;
  }

  public function getUserIdentifier(): string
  {
    return (string) $this->email;
  }

  public function getRoles(): array
  {
    $roles = [];

    if ($this->role === 'admin') {
      $roles[] = 'ROLE_ADMIN';
    } elseif ($this->role === 'client') {
      $roles[] = 'ROLE_CLIENT';
    } elseif ($this->role === 'doctor') {
      $roles[] = 'ROLE_DOCTOR';
    } elseif ($this->role === 'banque') {
      $roles[] = 'ROLE_BANQUE';
    } elseif ($this->role === 'cnts') {
      $roles[] = 'ROLE_CNTS';
    }

    $roles[] = 'ROLE_USER';

    return array_unique($roles);
  }

  public function eraseCredentials(): void
  {
    // clear temporary data if needed
  }
}
