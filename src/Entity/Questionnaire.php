<?php

namespace App\Entity;

use App\Repository\QuestionnaireRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: QuestionnaireRepository::class)]

class Questionnaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;



    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide")]   
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prenom ne peut pas être vide")]   
    private ?string $prenom = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "L'age ne peut pas être vide")]   
    #[Assert\Positive(message: "L'age ne peut pas être négatif")]   
    #[Assert\GreaterThanOrEqual(18, message: "L'âge doit être supérieur ou égal à 18 ans sinon vous n'etes pas eligible pour faire un don")] 
    #[Assert\LessThanOrEqual(value: 70, message: "L'age ne doit pas depasser 70 sinon vous n'etes pas eligible pour faire un don")]

    private ?int $age = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le sexe ne peut pas être vide")]   
    private ?string $sexe = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le poids ne peut pas être vide")]   
    #[Assert\Positive(message: "Le poids ne peut pas être négatif")] 
    #[Assert\GreaterThanOrEqual(50, message: "Le poids doit être supérieur ou égal à 50 kg sinon vous n'etes pas eligible pour faire un don")]
    #[Assert\LessThanOrEqual(100, message: "Le poids ne doit pas depasser 100 kg sinon vous n'etes pas eligible pour faire un don")]
    #[Assert\Regex(
        pattern: "/^[0-9]+(\.[0-9]+)?$/",
        message: "Le poids ne doit contenir que des chiffres (ex: 75.5)"    )]
    private ?float $poids = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: "vous avez depasser la limite de nombre de mots")]

    private ?string $autres = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\ManyToOne(inversedBy: 'questionnaires')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "veuillez choisir une campagne")]   

    private ?Compagne $campagne = null;

    #[ORM\OneToOne(mappedBy: 'questionnaire', cascade: ['persist', 'remove'])]
    private ?RendezVous $rendezVous = null;

    #[ORM\ManyToOne(inversedBy: 'questionnaires')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column(length: 5)]
    private ?string $group_sanguin = null;

   
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(string $sexe): static
    {
        $this->sexe = $sexe;

        return $this;
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(float $poids): static
    {
        $this->poids = $poids;

        return $this;
    }

    public function getAutres(): ?string
    {
        return $this->autres;
    }

    public function setAutres(?string $autres): static
    {
        $this->autres = $autres;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCampagne(): ?Compagne
    {
        return $this->campagne;
    }

    public function setCampagne(?Compagne $campagne): static
    {
        $this->campagne = $campagne;

        return $this;
    }

    public function getRendezVous(): ?RendezVous
    {
        return $this->rendezVous;
    }

    public function setRendezVous(?RendezVous $rendezVous): static
    {
        $this->rendezVous = $rendezVous;

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

    public function getGroupSanguin(): ?string
    {
        return $this->group_sanguin;
    }

    public function setGroupSanguin(string $group_sanguin): static
    {
        $this->group_sanguin = $group_sanguin;

        return $this;
    }


    

}
