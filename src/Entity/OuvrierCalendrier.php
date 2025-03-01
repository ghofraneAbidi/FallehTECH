<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\OuvrierCalendrierRepository;
use App\Entity\Utilisateur;
use App\Entity\Candidature;

#[ORM\Entity(repositoryClass: OuvrierCalendrierRepository::class)]
class OuvrierCalendrier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'calendrier')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $ouvrier = null;

    #[ORM\OneToOne(targetEntity: Candidature::class, inversedBy: 'calendar', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Candidature $candidature = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $endDate;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'en_attente'; // Default status is "pending"

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOuvrier(): ?Utilisateur
    {
        return $this->ouvrier;
    }

    public function setOuvrier(?Utilisateur $ouvrier): self
    {
        $this->ouvrier = $ouvrier;
        return $this;
    }

    public function getCandidature(): ?Candidature
    {
        return $this->candidature;
    }

    public function setCandidature(?Candidature $candidature): self
    {
        $this->candidature = $candidature;
        return $this;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): \DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }
}
