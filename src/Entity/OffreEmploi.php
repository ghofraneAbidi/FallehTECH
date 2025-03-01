<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Utilisateur;
use App\Entity\Land; // Import Land entity


#[ORM\Entity]
class OffreEmploi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null; // Primary Key

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $titre;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    private string $description;

    #[ORM\Column(type: "float")]
    #[Assert\NotBlank(message: "Le salaire est obligatoire.")]
    #[Assert\Positive(message: "Le salaire doit être un nombre positif.")]
    #[Assert\Range(
        min: 10,
        max: 10000,
        notInRangeMessage: "Le salaire doit être entre {{ min }} et {{ max }} DT."
    )]
    private float $salaire;
    
    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank(message: "Le lieu est obligatoire.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Le lieu ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $lieu;

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank(message: "La date de début est obligatoire.")]
    #[Assert\GreaterThan("today", message: "La date de début doit être dans le futur.")]
    private ?\DateTime $startDate = null;

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank(message: "La date d'expiration est obligatoire.")]
    #[Assert\GreaterThan(propertyPath: "startDate", message: "La date d'expiration doit être après la date de début.")]
    private ?\DateTime $dateExpiration = null;

    // ✅ Foreign Key: Link to Utilisateur (Employer/Farmer)
    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Utilisateur $id_employeur = null;

    /**
     * @var Collection<int, Candidature>
     */
    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'idOffre', orphanRemoval: true)]
    private Collection $candidatures;


    public function __construct()
    {
        $this->candidatures = new ArrayCollection();
        $this->startDate = new \DateTime(); // Default to today
        $this->dateExpiration = (new \DateTime())->modify('+1 month'); // Default to 1 month from today
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }

    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): self { $this->titre = $titre; return $this; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getSalaire(): float { return $this->salaire; }
    public function setSalaire(float $salaire): self { $this->salaire = $salaire; return $this; }

    public function getLieu(): string { return $this->lieu; }
    public function setLieu(string $lieu): self { $this->lieu = $lieu; return $this; }

    public function getStartDate(): ?\DateTime { return $this->startDate; }
    public function setStartDate(?\DateTime $startDate): self { 
        $this->startDate = $startDate ?? new \DateTime(); // Default to today if null
        return $this; 
    }

    public function getDateExpiration(): ?\DateTime { return $this->dateExpiration; }
    public function setDateExpiration(?\DateTime $dateExpiration): self { 
        $this->dateExpiration = $dateExpiration ?? (new \DateTime())->modify('+1 month'); // Default to 1 month ahead if null
        return $this; 
    }

    public function getIdEmployeur(): ?Utilisateur { return $this->id_employeur; }
    public function setIdEmployeur(?Utilisateur $id_employeur): self { $this->id_employeur = $id_employeur; return $this; }

    /**
     * @return Collection<int, Candidature>
     */
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): static
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures->add($candidature);
            $candidature->setIdOffre($this);
        }

        return $this;
    }

    public function removeCandidature(Candidature $candidature): static
    {
        if ($this->candidatures->removeElement($candidature)) {
            // set the owning side to null (unless already changed)
            if ($candidature->getIdOffre() === $this) {
                $candidature->setIdOffre(null);
            }
        }

        return $this;
    }
    
    public function __toString(): string
    {
        return $this->titre; // Return the job title or any meaningful string representation
    }
}
