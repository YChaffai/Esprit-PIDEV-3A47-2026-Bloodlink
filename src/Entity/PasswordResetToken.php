<?php

namespace App\Entity;

use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PasswordResetTokenRepository::class)]
class PasswordResetToken
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\ManyToOne(targetEntity: User::class)]
  #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
  private ?User $user = null;

  #[ORM\Column(length: 255)]
  private ?string $token = null;

  #[ORM\Column]
  private ?\DateTimeImmutable $expiresAt = null;

  #[ORM\Column]
  private ?\DateTimeImmutable $createdAt = null;

  #[ORM\Column]
  private bool $used = false;

  public function __construct()
  {
    $this->createdAt = new \DateTimeImmutable();
    $this->expiresAt = new \DateTimeImmutable('+1 hour');
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

  public function getToken(): ?string
  {
    return $this->token;
  }

  public function setToken(string $token): static
  {
    $this->token = $token;
    return $this;
  }

  public function getExpiresAt(): ?\DateTimeImmutable
  {
    return $this->expiresAt;
  }

  public function setExpiresAt(\DateTimeImmutable $expiresAt): static
  {
    $this->expiresAt = $expiresAt;
    return $this;
  }

  public function getCreatedAt(): ?\DateTimeImmutable
  {
    return $this->createdAt;
  }

  public function isExpired(): bool
  {
    return new \DateTimeImmutable() > $this->expiresAt;
  }

  public function isUsed(): bool
  {
    return $this->used;
  }

  public function setUsed(bool $used): static
  {
    $this->used = $used;
    return $this;
  }
}
