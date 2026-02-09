<?php

namespace App\Entity;

use App\Repository\DonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DonRepository::class)]
class Don
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull(message: "La date est obligatoire.")]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La quantité est obligatoire.")]
    #[Assert\Positive(message: "La quantité doit être positive.")]
    #[Assert\NotBlank(message: "La quantité est obligatoire.")]
    #[Assert\Range(
        min: 50,
        max: 1000,
        notInRangeMessage: "Quantité invalide (50–1000 ml)."
    )]
    private ?float $quantite = null;

    #[ORM\Column(name: 'type_don', length: 255)]
    #[Assert\NotBlank(message: "Le type de don est obligatoire.")]
    #[Assert\Choice(
        choices: ["Sang total", "Plasma", "Plaquettes", "Globules rouges"],
        message: "Type de don invalide."
    )]
    private ?string $typeDon = null;

    #[ORM\Column(name: 'id_entite')]
    #[Assert\NotNull(message: "L'entité est obligatoire.")]
    #[Assert\Positive(message: "ID entité invalide.")]
    private ?int $idEntite = 1;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: "Le client est obligatoire.")]
    private ?Client $client = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(float $quantite): static
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getTypeDon(): ?string
    {
        return $this->typeDon;
    }

    public function setTypeDon(string $typeDon): static
    {
        $this->typeDon = $typeDon;
        return $this;
    }

    public function getIdEntite(): ?int
    {
        return $this->idEntite;
    }

    public function setIdEntite(int $idEntite): static
    {
        $this->idEntite = $idEntite;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    /**
     * ✅ Fix for: "Object of class App\Entity\Don could not be converted to string"
     * EasyAdmin + Symfony forms need a string label for entity choices.
     */
    public function __toString(): string
    {
        $id = $this->id ?? 0;

        $type = $this->typeDon ?: 'Don';
        $date = $this->date ? $this->date->format('Y-m-d') : 'no-date';

        $q = $this->quantite !== null
            ? rtrim(rtrim(number_format((float) $this->quantite, 2, '.', ''), '0'), '.') . ' ml'
            : null;

        return $q
            ? sprintf('#%d • %s • %s • %s', $id, $type, $date, $q)
            : sprintf('#%d • %s • %s', $id, $type, $date);
    }
}
