<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "veuillez saisir un email")]  
    #[Assert\Email(message: "L'adresse email '{{ value }}' n'est pas un email valide.")] 

    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $type_sang = null;

    #[ORM\Column]
    private ?\DateTime $dernier_don = null;

    /**
     * @var Collection<int, Questionnaire>
     */
    #[ORM\OneToMany(targetEntity: Questionnaire::class, mappedBy: 'client', orphanRemoval: true)]
    private Collection $questionnaires;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telephone = null;

    public function __construct()
    {
        $this->questionnaires = new ArrayCollection();
    }


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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTypeSang(): ?string
    {
        return $this->type_sang;
    }

    public function setTypeSang(string $type_sang): static
    {
        $this->type_sang = $type_sang;

        return $this;
    }

    public function getDernierDon(): ?\DateTime
    {
        return $this->dernier_don;
    }

    public function setDernierDon(\DateTime $dernier_don): static
    {
        $this->dernier_don = $dernier_don;

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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

   
}
