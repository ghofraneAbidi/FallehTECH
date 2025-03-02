<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Column(type: 'float')]
    private ?float $area = null;

    #[ORM\OneToMany(mappedBy: 'land', targetEntity: Point::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $points;

    public function __construct()
    {
        $this->points = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getOwner(): ?Utilisateur { return $this->owner; }

    public function setOwner(?Utilisateur $owner): self
    {
        $this->owner = $owner;
        return $this;
    }

    public function getName(): ?string { return $this->name; }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string { return $this->description; }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getArea(): ?float { return $this->area; }

    public function setArea(float $area): self
    {
        $this->area = $area;
        return $this;
    }

    public function getPoints(): Collection { return $this->points; }

    public function addPoint(Point $point): self
    {
        if (!$this->points->contains($point)) {
            $this->points->add($point);
            $point->setLand($this);
        }
        return $this;
    }

    public function removePoint(Point $point): self
    {
        if ($this->points->removeElement($point)) {
            if ($point->getLand() === $this) {
                $point->setLand(null);
            }
        }
        return $this;
    }
}
