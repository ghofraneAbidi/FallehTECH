<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Utilisateur;

#[ORM\Entity]
class OffreEmploi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null; // Primary Key

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank]
    private string $titre;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank]
    private string $description;

    #[ORM\Column(type: "float")]
    #[Assert\Positive]
    private float $salaire;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    private string $lieu;

    #[ORM\Column(type: "date")]
    #[Assert\GreaterThan("today")]
    private \DateTime $dateExpiration;

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

    public function getDateExpiration(): \DateTime { return $this->dateExpiration; }
    public function setDateExpiration(\DateTime $dateExpiration): self { $this->dateExpiration = $dateExpiration; return $this; }

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
}
