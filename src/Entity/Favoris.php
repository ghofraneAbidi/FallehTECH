<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\Produit;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Favoris
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Produit::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")] // Deletes favoris if produit is deleted
    private ?Produit $produit = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "favoris")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")] // Deletes favoris if user is deleted
    private ?User $user = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    // ✅ Getter for Produit
    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    // ✅ Setter for Produit
    public function setProduit(?Produit $produit): self
    {
        $this->produit = $produit;
        return $this;
    }

    // ✅ Getter for User
    public function getUser(): ?User
    {
        return $this->user;
    }

    // ✅ Setter for User
    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    // ✅ Getter for createdAt
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    // ✅ Setter for createdAt
    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
