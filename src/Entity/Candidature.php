<?php

namespace App\Entity;

use App\Repository\CandidatureRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\StatutCandidature; // Import the Enum
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: CandidatureRepository::class)]
class Candidature
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $idTravailleur = null;

    #[ORM\ManyToOne(inversedBy: 'candidatures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?OffreEmploi $idOffre = null;

    #[ORM\Column(enumType: StatutCandidature::class)]
    private StatutCandidature $statut;

    #[ORM\Column(type: 'datetime_immutable')]
    private ?DateTimeImmutable $dateApplied;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $rating = null;

    public function __construct()
    {
        $this->dateApplied = new DateTimeImmutable(); // Automatically set the application date
        $this->statut = StatutCandidature::EN_ATTENTE; // Default status
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdTravailleur(): ?Utilisateur
    {
        return $this->idTravailleur;
    }

    public function setIdTravailleur(?Utilisateur $idTravailleur): static
    {
        $this->idTravailleur = $idTravailleur;
        return $this;
    }

    public function getIdOffre(): ?OffreEmploi
    {
        return $this->idOffre;
    }

    public function setIdOffre(?OffreEmploi $idOffre): static
    {
        $this->idOffre = $idOffre;
        return $this;
    }

    public function getStatut(): StatutCandidature
    {
        return $this->statut;
    }

    public function setStatut(StatutCandidature $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    public function getDateApplied(): ?DateTimeImmutable
    {
        return $this->dateApplied;
    }

    public function setDateApplied(DateTimeImmutable $dateApplied): static
    {
        $this->dateApplied = $dateApplied;
        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): static
    {
        $this->rating = $rating;
        return $this;
    }
}
