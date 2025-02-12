<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    private ?string $last_name = null;

    #[ORM\Column(length: 50)]
    private ?string $email = null;

    #[ORM\Column(length: 50)]
    private ?string $password = null;

    #[ORM\Column(length: 20)]
    private ?string $role = null;

    #[ORM\Column(type: Types::BLOB)]
    private $carte_identite;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $disponibility = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $experience = null;

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

    public function getCarteIdentite()
    {
        return $this->carte_identite;
    }

    public function setCarteIdentite($carte_identite): static
    {
        $this->carte_identite = $carte_identite;

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

    // Implementing the `getRoles` method from `UserInterface`
    public function getRoles(): array
    {
        // Ensure the roles are returned as an array
        // Default role is "ROLE_USER"
        $roles = [$this->role];

        // You can add other roles here if needed, for example:
        // if ($this->role === 'admin') {
        //     $roles[] = 'ROLE_ADMIN';
        // }

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
