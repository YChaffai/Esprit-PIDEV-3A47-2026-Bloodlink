<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: "type_sang", length: 10)]
    private ?string $typeSang = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dernierDon = null;

    // ❌ REMOVED: Telephone field is gone

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Don::class)]
    private Collection $dons;

    #[ORM\OneToOne(targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: "id", referencedColumnName: "id", nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->dons = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getTypeSang(): ?string { return $this->typeSang; }
    public function setTypeSang(string $typeSang): static { $this->typeSang = $typeSang; return $this; }

    public function getDernierDon(): ?\DateTimeInterface { return $this->dernierDon; }
    public function setDernierDon(?\DateTimeInterface $dernierDon): static { $this->dernierDon = $dernierDon; return $this; }

    public function getDons(): Collection { return $this->dons; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(User $user): static { $this->user = $user; return $this; }

    // --- PROXY METHODS (Keep these so Name/Email still work) ---

    public function getNom(): string 
    { 
        return $this->user ? $this->user->getNom() : ''; 
    }

    public function setNom(string $nom): static
    {
        if (!$this->user) { $this->user = new User(); }
        // Set default password so DB doesn't complain
        if (!$this->user->getPassword()) {
             $this->user->setPassword('12345678'); 
             $this->user->setRole('client');
        }
        $this->user->setNom($nom);
        return $this;
    }

    public function getPrenom(): string 
    { 
        return $this->user ? $this->user->getPrenom() : ''; 
    }

    public function setPrenom(string $prenom): static
    {
        if (!$this->user) { $this->user = new User(); }
        if (!$this->user->getPassword()) {
             $this->user->setPassword('12345678'); 
             $this->user->setRole('client');
        }
        $this->user->setPrenom($prenom);
        return $this;
    }

    public function getEmail(): string 
    { 
        return $this->user ? $this->user->getEmail() : ''; 
    }

    public function setEmail(string $email): static
    {
        if (!$this->user) { $this->user = new User(); }
        $this->user->setEmail($email);
        return $this;
    }

    public function __toString(): string
    {
        return $this->user ? sprintf('%s %s (#%d)', $this->getPrenom(), $this->getNom(), $this->id) : ('Client #' . $this->id);
    }
}