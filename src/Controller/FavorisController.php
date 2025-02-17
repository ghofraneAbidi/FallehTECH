<?php
namespace App\Controller;

use App\Entity\Favoris;
use App\Entity\Produit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FavorisRepository;
use Symfony\Component\HttpFoundation\Response;

class FavorisController extends AbstractController
{
    // Route pour ajouter un produit aux favoris
    // FavorisController.php

#[Route('/produit/{id}/add-to-favorites', name: 'add_to_favorites', methods: ['POST'])]
public function addToFavorites(Produit $produit, EntityManagerInterface $em, Request $request): JsonResponse
{
    // Check if product exists
    if (!$produit) {
        return new JsonResponse(['success' => false, 'message' => 'Product not found'], 404);
    }

    // Check if the product is already in favorites
    $existingFavoris = $em->getRepository(Favoris::class)->findOneBy(['produit' => $produit, 'isFavorite' => true, 'userId' => 1]);
    if ($existingFavoris) {
        return new JsonResponse(['success' => false, 'message' => 'Product is already in favorites']);
    }

    // Create a new favoris entry
    $favoris = new Favoris();
    $favoris->setProduit($produit);
    $favoris->setIsFavorite(true);
    $favoris->setUserId(1);  // Static user ID (can be dynamic based on the logged-in user)

    // Persist and flush the changes
    $em->persist($favoris);
    $em->flush();

    return new JsonResponse(['success' => true, 'message' => 'Product added to favorites']);
}

    // Get all favorites for the static user
    #[Route('/favoris/list', name: 'favoris_list')]
    public function showFavoris(FavorisRepository $favorisRepo): Response
    {
        // Static userId for simplicity
        $userId = 1;

        // Fetch favoris for the static user
        $favorisList = $favorisRepo->findFavoritesByUser($userId);

        // Render the view and pass the favoris list to Twig
        return $this->render('favoris/list.html.twig', [
            'favorisList' => $favorisList,
        ]);
    }
}

