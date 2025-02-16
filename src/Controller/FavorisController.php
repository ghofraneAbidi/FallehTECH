<?php
namespace App\Controller;

use App\Entity\Favoris;
use App\Entity\Produit;
use App\Repository\FavorisRepository;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FavorisController extends AbstractController
{
    #[Route('/favoris/add/{id}', name: 'add_favoris')]
    public function addFavoris(Produit $produit, ProduitRepository $produitRepo, UserRepository $userRepo, FavorisRepository $favorisRepo): Response
    {
        // Get the current user
        $user = $this->getUser();

        // Check if the product is already in the favorites
        $existingFavoris = $favorisRepo->findOneBy(['produit' => $produit, 'user' => $user]);

        if (!$existingFavoris) {
            // Create a new favoris entity and add it to the database
            $favoris = new Favoris();
            $favoris->setProduit($produit);
            $favoris->setUser($user);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($favoris);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté aux favoris!');
        } else {
            $this->addFlash('warning', 'Ce produit est déjà dans vos favoris!');
        }

        return $this->redirectToRoute('product_list');  // Change to your product list page
    }

    #[Route('/favoris/remove/{id}', name: 'remove_favoris')]
    public function removeFavoris(Favoris $favoris, FavorisRepository $favorisRepo): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($favoris);
        $entityManager->flush();

        $this->addFlash('success', 'Produit retiré des favoris!');
        return $this->redirectToRoute('product_list');  // Change to your product list page
    }

    #[Route('/favoris/list', name: 'favoris_list')]
public function favorisList(FavorisRepository $favorisRepo): Response
{
    // Get current user
    $user = $this->getUser();

    // Fetch favorites for the logged-in user
    $favorisList = $favorisRepo->findBy(['user' => $user]);

    // Return the favorites data to the view
    return $this->render('favoris/list.html.twig', [
        'favorisList' => $favorisList,
    ]);
}

}
