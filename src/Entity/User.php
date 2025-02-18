<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

 
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Veuillez entrer votre nom")]
    #[Assert\Length(min: 2, max: 50, minMessage: "Le nom doit comporter au moins {{ limit }} caractères", maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères")]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Veuillez entrer votre prénom")]
    #[Assert\Length(min: 2, max: 50, minMessage: "Le prénom doit comporter au moins {{ limit }} caractères", maxMessage: "Le prénom ne peut pas dépasser {{ limit }} caractères")]
    private ?string $last_name = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Veuillez entrer votre email")]
    #[Assert\Email(message: "L'email doit être sous la forme: exemple@exemple.exemple")]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Veuillez entrer votre mot de passe")]
    #[Assert\Length(min: 8, max: 20, minMessage: "Le mot de passe doit comporter au moins {{ limit }} caractères", maxMessage: "Le mot de passe ne peut pas dépasser {{ limit }} caractères")]
    #[Assert\Regex(
        pattern: "/^(?=.*[A-Z])(?=.*\d).+$/",
        message: "Le mot de passe doit contenir au moins une lettre majuscule et un chiffre"
    )]
    private ?string $password = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: "Veuillez spécifier votre rôle")]
    private ?string $role = null;

    #[ORM\Column(length: 8, nullable: false)]
    #[Assert\NotBlank(message: "Veuillez entrer votre numéro de carte d'identité")]
    #[Assert\Length(
        min: 8, 
        max: 8, 
        exactMessage: "Le numéro de carte d'identité doit contenir exactement {{ limit }} chiffres"
    )]
    #[Assert\Regex(
        pattern: "/^\d{8}$/",
        message: "Le numéro de carte d'identité ne doit contenir que des chiffres"
    )]
    private ?string $carte_identite = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\Date(message: "La date de disponibilité doit être valide")]
    private ?\DateTimeInterface $disponibility = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $experience = null;

    #[ORM\Column(type: 'boolean')]
    private bool $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): static
    {
        $this->last_name = $last_name;

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

    public function getPassword(): ?string
    {
        return $this->password;
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

    public function getCarteIdentite(): ?string
    {
        return $this->carte_identite;
    }

    public function setCarteIdentite(?string $carteIdentite): self
    {
        $this->carte_identite = $carteIdentite;

        return $this;
    }

    public function getDisponibility(): ?\DateTimeInterface
    {
        return $this->disponibility;
    }

    public function setDisponibility(?\DateTimeInterface $disponibility): static
    {
        $this->disponibility = $disponibility;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getExperience(): ?string
    {
        return $this->experience;
    }

    public function setExperience(?string $experience): static
    {
        $this->experience = $experience;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    // Implementing the `getRoles` method from `UserInterface`
    public function getRoles(): array
    {
        $roles = [$this->role];
    
        if ($this->role === 'admin') {  // Si ton rôle admin est stocké comme "admin"
            $roles[] = 'ROLE_ADMIN';
        } else {
            $roles[] = 'ROLE_USER';
        }
    
        return array_unique($roles);
    }

    public function eraseCredentials(): void
    {
        // If you store sensitive data like plaintext passwords, you can clear them here.
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }
}
