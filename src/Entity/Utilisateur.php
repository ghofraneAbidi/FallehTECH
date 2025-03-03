<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
class Utilisateur implements UserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $role = null;

    #[ORM\OneToMany(mappedBy: "user", targetEntity: Task::class, cascade: ["persist", "remove"])]
    private Collection $tasks;

    #[ORM\OneToMany(mappedBy: "owner", targetEntity: Land::class, cascade: ["persist", "remove"])]
    private Collection $lands;

    #[ORM\OneToMany(mappedBy: "ouvrier", targetEntity: OuvrierCalendrier::class, cascade: ["persist", "remove"])]
    private Collection $calendrier;

    #[ORM\OneToMany(targetEntity: Candidature::class, mappedBy: 'idTravailleur', orphanRemoval: true)]
    private Collection $candidatures;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
        $this->lands = new ArrayCollection();
        $this->calendrier = new ArrayCollection();
        $this->candidatures = new ArrayCollection();
    }

    // ✅ Getters and Setters for Basic Fields
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
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

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    // ✅ Methods for Symfony Security User Interface
    public function getUserIdentifier(): string
    {
        return $this->email; // Use email as unique identifier
    }

    public function getRoles(): array
    {
        return [$this->role]; // Symfony expects an array
    }

    public function eraseCredentials(): void
    {
        // If storing temporary sensitive data, clear it here.
    }

    // ✅ Relationship with Tasks (One-To-Many)
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setUser($this);
        }
        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }
        return $this;
    }

    // ✅ Relationship with Lands (One-To-Many)
    public function getLands(): Collection
    {
        return $this->lands;
    }

    public function addLand(Land $land): self
    {
        if (!$this->lands->contains($land)) {
            $this->lands->add($land);
            $land->setOwner($this);
        }
        return $this;
    }

    public function removeLand(Land $land): self
    {
        if ($this->lands->removeElement($land)) {
            if ($land->getOwner() === $this) {
                $land->setOwner(null);
            }
        }
        return $this;
    }

    // ✅ Relationship with Calendrier (One-To-Many)
    public function getCalendrier(): Collection
    {
        return $this->calendrier;
    }

    public function addCalendrier(OuvrierCalendrier $calendrier): self
    {
        if (!$this->calendrier->contains($calendrier)) {
            $this->calendrier->add($calendrier);
            $calendrier->setOuvrier($this);
        }
        return $this;
    }

    public function removeCalendrier(OuvrierCalendrier $calendrier): self
    {
        if ($this->calendrier->removeElement($calendrier)) {
            if ($calendrier->getOuvrier() === $this) {
                $calendrier->setOuvrier(null);
            }
        }
        return $this;
    }

    // ✅ Relationship with Candidatures (One-To-Many)
    public function getCandidatures(): Collection
    {
        return $this->candidatures;
    }

    public function addCandidature(Candidature $candidature): self
    {
        if (!$this->candidatures->contains($candidature)) {
            $this->candidatures->add($candidature);
            $candidature->setIdTravailleur($this);
        }
        return $this;
    }

    public function removeCandidature(Candidature $candidature): self
    {
        if ($this->candidatures->removeElement($candidature)) {
            if ($candidature->getIdTravailleur() === $this) {
                $candidature->setIdTravailleur(null);
            }
        }
        return $this;
    }
}
