<?php

namespace App\Entity;

use App\Repository\DossierMedRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DossierMedRepository::class)]
#[ORM\Table(name: 'dossier_med')]
class DossierMed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // DB: float NOT NULL
    #[ORM\Column]
    #[Assert\NotNull(message: "La taille est obligatoire.")]
    #[Assert\Positive(message: "La taille doit être positive.")]
    #[Assert\Range(
        min: 50,
        max: 260,
        notInRangeMessage: "Taille invalide (50–260 cm)."
    )]
    private ?float $taille = null;

    // DB: poid NOT NULL
    #[ORM\Column(name: 'poid')]
    #[Assert\NotNull(message: "Le poids est obligatoire.")]
    #[Assert\Positive(message: "Le poids doit être positif.")]
    #[Assert\Range(
        min: 10,
        max: 400,
        notInRangeMessage: "Poids invalide (10–400 kg)."
    )]
    private ?float $poid = null;

    // DB: float NOT NULL
    #[ORM\Column]
    #[Assert\NotNull(message: "La température est obligatoire.")]
    #[Assert\Range(
        min: 30,
        max: 45,
        notInRangeMessage: "Température invalide (30–45 °C)."
    )]
    private ?float $temperature = null;

    // DB: varchar(255) NOT NULL
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le sexe est obligatoire.")]
    #[Assert\Choice(
        choices: ["Homme", "Femme", "Autre"],
        message: "Sexe invalide."
    )]
    private ?string $sexe = null;

    // DB: contact_urgence int NOT NULL
    #[ORM\Column(name: 'contact_urgence')]
    #[Assert\NotNull(message: "Le contact d'urgence est obligatoire.")]
    #[Assert\Positive(message: "Le contact d'urgence doit être positif.")]
    #[Assert\Range(
        min: 100000,
        max: 999999999999999,
        notInRangeMessage: "Contact urgence invalide (6 à 15 chiffres)."
    )]
    private ?int $contactUrgence = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le nom est trop court.",
        maxMessage: "Le nom est trop long."
    )]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le prénom est obligatoire.")]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: "Le prénom est trop court.",
        maxMessage: "Le prénom est trop long."
    )]
    private ?string $prenom = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "L'âge est obligatoire.")]
    #[Assert\PositiveOrZero(message: "L'âge doit être >= 0.")]
    #[Assert\Range(
        min: 0,
        max: 120,
        notInRangeMessage: "Âge invalide (0–120)."
    )]
    private ?int $age = null;

    // DB: type_sang varchar(255) NOT NULL
    #[ORM\Column(name: 'type_sang', length: 255)]
    #[Assert\NotBlank(message: "Le groupe sanguin est obligatoire.")]
    #[Assert\Choice(
        choices: ["A+","A-","B+","B-","AB+","AB-","O+","O-"],
        message: "Groupe sanguin invalide."
    )]
    private ?string $typeSang = null;

    // DB: id_client int NOT NULL (FK)
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_client', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: "Le client est obligatoire.")]
    private ?Client $client = null;

    // DB: id_don int NOT NULL (FK)
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(name: 'id_don', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: "Le don lié est obligatoire.")]
    private ?Don $don = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaille(): ?float
    {
        return $this->taille;
    }

    public function setTaille(float $taille): self
    {
        $this->taille = $taille;
        return $this;
    }

    public function getPoid(): ?float
    {
        return $this->poid;
    }

    public function setPoid(float $poid): self
    {
        $this->poid = $poid;
        return $this;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(string $sexe): self
    {
        $this->sexe = $sexe;
        return $this;
    }

    public function getContactUrgence(): ?int
    {
        return $this->contactUrgence;
    }

    public function setContactUrgence(int $contactUrgence): self
    {
        $this->contactUrgence = $contactUrgence;
        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;
        return $this;
    }

    public function getTypeSang(): ?string
    {
        return $this->typeSang;
    }

    public function setTypeSang(string $typeSang): self
    {
        $this->typeSang = $typeSang;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getDon(): ?Don
    {
        return $this->don;
    }

    public function setDon(?Don $don): self
    {
        $this->don = $don;
        return $this;
    }
}
