<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[UniqueEntity(fields: ['reference'], message: 'Cette référence existe déjà.')]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La référence est obligatoire.')]
    #[Assert\Positive(message: 'La référence doit être positive.')]
    private ?int $reference = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'La quantité est obligatoire.')]
    #[Assert\Positive(message: 'La quantité doit être supérieure à 0.')]
    private ?int $quantite = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La priorité est obligatoire.')]
    #[Assert\Choice(choices: ['Faible', 'Élevée', 'Urgente'],message: 'Priorité invalide. Choisissez: Faible, Élevée, Urgente.')]
    private ?string $priorite = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le type de sang est obligatoire.')]
    #[Assert\Choice(choices: ['A+','A-','B+','B-','AB+','AB-','O+','O-'], message: 'Type de sang invalide.')]
    private ?string $type_sang = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La banque est obligatoire.')]
    private ?Banque $banque = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Le client est obligatoire.')]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Stock $stock = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le statut est obligatoire.')]
    #[Assert\Choice(
        choices: ['En Attente', 'Confirmée', 'Annulée'],
        message: 'Statut invalide. Choisissez: En Attente, Confirmée, Annulée'
    )]
    private ?string $status = 'En Attente';
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (!$this->status) {
            $this->status = 'Pending';
        }
    }

    #[Assert\Callback]
    public function validateBusinessRules(ExecutionContextInterface $context): void
    {
        if ($this->stock && $this->quantite !== null) {
            if ($this->quantite > $this->stock->getQuantite()) {
                $context->buildViolation('Quantité demandée dépasse le stock disponible ({{ s }}).')
                    ->setParameter('{{ s }}', (string) $this->stock->getQuantite())
                    ->atPath('quantite')
                    ->addViolation();
            }
        }

        if ($this->stock && $this->type_sang) {
            if ($this->stock->getTypeSang() !== $this->type_sang) {
                $context->buildViolation('Le stock sélectionné est de type {{ t }}.')
                    ->setParameter('{{ t }}', (string) $this->stock->getTypeSang())
                    ->atPath('stock')
                    ->addViolation();
            }
        }
    }
    





    
    public function getId(): ?int { return $this->id; }
    public function getReference(): ?int { return $this->reference; }
    public function setReference(?int $reference): static { $this->reference = $reference; return $this; }

    public function getQuantite(): ?int { return $this->quantite; }
    public function setQuantite(?int $quantite): static { $this->quantite = $quantite; return $this; }

    public function getPriorite(): ?string { return $this->priorite; }
    public function setPriorite(?string $priorite): static { $this->priorite = $priorite; return $this; }

    public function getTypeSang(): ?string { return $this->type_sang; }
    public function setTypeSang(?string $type_sang): static { $this->type_sang = $type_sang; return $this; }

    public function getBanque(): ?Banque { return $this->banque; }
    public function setBanque(?Banque $banque): static { $this->banque = $banque; return $this; }

    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }

    public function getStock(): ?Stock { return $this->stock; }
    public function setStock(?Stock $stock): static { $this->stock = $stock; return $this; }
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }
}