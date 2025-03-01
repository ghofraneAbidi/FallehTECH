<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use App\Repository\LandRepository;

#[ORM\Entity(repositoryClass: LandRepository::class)]
class Land
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class, inversedBy: 'lands')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Utilisateur $owner = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'geometry', options: ['geometry_type' => 'POLYGON'])]
    private $coordinates;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?Utilisateur
    {
        return $this->owner;
    }

    public function setOwner(?Utilisateur $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCoordinates()
    {
        return $this->coordinates;
    }

    public function setCoordinates($coordinates): self
    {
        $this->coordinates = $coordinates;
        return $this;
    }
}
