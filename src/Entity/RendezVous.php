<?php

namespace App\Entity;

use App\Repository\RendezVousRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Entity\Questionnaire;
use App\Entity\Campagne;

#[ORM\Entity(repositoryClass: RendezVousRepository::class)]
class RendezVous
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'rendezVous')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Questionnaire $questionnaire = null;

    #[ORM\Column]
    private ?\DateTime $date_don = null;

    #[Assert\Callback]
public function validateUniqueDatePerCampagne(ExecutionContextInterface $context, $payload): void
{
    if (!$this->date_don) {
        $context->buildViolation('Vous devez choisir une date de rendez-vous.')
            ->atPath('date_don')
            ->addViolation();
        return; 
    }
    
    // 1. Nouvelle règle : Interdiction entre 12h et 14h
    $heure = (int) $this->date_don->format('H');
    if ($heure >= 12 && $heure < 14 || $heure<8 || $heure>17) {
        $context->buildViolation('veuillez choisir un rendez vous de 8h à 12h ou de 14h à 18h')
            ->atPath('date_don')
            ->addViolation();
        return; // On arrête tout de suite si l'horaire est invalide
    }

    $questionnaire = $this->getQuestionnaire();
    $campagne = $questionnaire?->getCampagne();
    $currentEntite = $this->getEntite();

    if (!$campagne || !$currentEntite) {
        return;
    }

    $targetDate = $this->date_don->format('Y-m-d H:i');

    // 2. Vérification des doublons sur la même entité
    foreach ($campagne->getQuestionnaires() as $q) {
        $existingRDV = $q->getRendezVous(); 
        
        if ($existingRDV && $existingRDV !== $this) {
            $sameDate = $existingRDV->getDateDon()->format('Y-m-d H:i') === $targetDate;
            $sameEntite = $existingRDV->getEntite() === $currentEntite;

            if ($sameDate && $sameEntite) {
                $context->buildViolation('Ce créneau est déjà réservé pour cette entité de collecte.')
                    ->atPath('date_don')
                    ->addViolation();
                return;
            }
        }
    }
}

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Vous devez choisir une entite")]  
    private ?EntiteCollecte $entite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionnaire(): ?Questionnaire
    {
        return $this->questionnaire;
    }

    public function setQuestionnaire(?Questionnaire $questionnaire): static
    {
        $this->questionnaire = $questionnaire;

        return $this;
    }

    public function getDateDon(): ?\DateTime
    {
        return $this->date_don;
    }

    public function setDateDon(?\DateTime $date_don): static
    {
        $this->date_don = $date_don;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getEntite(): ?EntiteCollecte
    {
        return $this->entite;
    }

    public function setEntite(?EntiteCollecte $entite): static
    {
        $this->entite = $entite;

        return $this;
    }


 
}
