<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[Vich\Uploadable]  // Required for VichUploader
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le nom du produit est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le nom ne doit pas dépasser {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9À-ÿ\s\-]+$/",
        message: "Le nom ne doit contenir que des lettres, des chiffres, des espaces et des tirets."
    )]
    private ?string $nom = null;

    #[ORM\Column(type: "decimal", scale: 2)]
    #[Assert\NotBlank(message: "Le prix est obligatoire.")]
    #[Assert\GreaterThan(
        value: 1,
        message: "Le prix doit être supérieur à 1 DT."
    )]
    #[Assert\Regex(
        pattern: "/^\d+(\.\d{1,2})?$/",
        message: "Le prix doit être un nombre avec un maximum de deux décimales."
    )]
    private ?float $prix = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "La description doit contenir au moins {{ limit }} caractères."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9À-ÿ\s.,'!?-]+$/",
        message: "La description ne doit contenir que des lettres, chiffres, ponctuation et espaces."
    )]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Categorie::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Veuillez sélectionner une catégorie.")]
    private ?Categorie $categorie = null;

    #[ORM\ManyToOne(targetEntity: SousCategorie::class, inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: "Veuillez sélectionner une sous-catégorie.")]
    private ?SousCategorie $sousCategorie = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[Vich\UploadableField(mapping: 'produit_images', fileNameProperty: 'image')]
    #[Assert\Image(
        maxSize: "2M",
        mimeTypes: ["image/jpeg", "image/png"],
        mimeTypesMessage: "Veuillez télécharger une image valide (JPEG ou PNG)."
    )]
    private ?File $imageFile = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isFavorite = false;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\Choice(
        choices: ['En stock', 'Rupture de stock'],
        message: "Le stock doit être 'En stock' ou 'Rupture de stock'."
    )]
    private ?string $stock = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // ✅ Getters and Setters remain unchanged
    public function getId(): ?int { return $this->id; }
    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getPrix(): ?float { return $this->prix; }
    public function setPrix(float $prix): self { $this->prix = $prix; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }

    public function getCategorie(): ?Categorie { return $this->categorie; }
    public function setCategorie(?Categorie $categorie): self { $this->categorie = $categorie; return $this; }

    public function getSousCategorie(): ?SousCategorie { return $this->sousCategorie; }
    public function setSousCategorie(?SousCategorie $sousCategorie): self { $this->sousCategorie = $sousCategorie; return $this; }

    public function getImage(): ?string { return $this->image; }
    public function setImage(?string $image): self { $this->image = $image; return $this; }

    public function getImageFile(): ?File { return $this->imageFile; }
    public function setImageFile(?File $imageFile = null): void {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getStock(): ?string { return $this->stock; }
    public function setStock(string $stock): self { $this->stock = $stock; return $this; }

    public function getIsFavorite(): bool { return $this->isFavorite; }
    public function setIsFavorite(bool $isFavorite): self { $this->isFavorite = $isFavorite; return $this; }
}
