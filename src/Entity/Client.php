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

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Don::class)]
    private Collection $dons;

    public function __construct()
    {
        $this->dons = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getTypeSang(): ?string { return $this->typeSang; }
    public function setTypeSang(string $typeSang): static { $this->typeSang = $typeSang; return $this; }

    public function getDernierDon(): ?\DateTimeInterface { return $this->dernierDon; }
    public function setDernierDon(?\DateTimeInterface $dernierDon): static { $this->dernierDon = $dernierDon; return $this; }

    /** @return Collection<int, Don> */
    public function getDons(): Collection { return $this->dons; }

    public function __toString(): string
    {
        return $this->typeSang ?? ('Client #' . $this->id);
    }
}
