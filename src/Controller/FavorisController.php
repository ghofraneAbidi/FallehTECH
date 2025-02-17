<?php
namespace App\Controller;

use App\Entity\Favoris; // ✅ Add this line
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
    #[Route('/produit/{id}/add-to-favorites', name: 'add_to_favorites', methods: ['POST'])]
    public function addToFavorites(Produit $produit, EntityManagerInterface $em, Request $request): JsonResponse
{
    try {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'] ?? 1; // Change to dynamic user ID if authentication is available

        error_log("Received favorite request for Produit ID: " . $produit->getId() . " by User ID: " . $userId);

        // Check if the product is already in favorites
        $existingFavoris = $em->getRepository(Favoris::class)->findOneBy([
            'produit' => $produit,
            'userId' => $userId,
        ]);

        if ($existingFavoris) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Produit déjà dans les favoris',
                'notification' => 'error'
            ]);
        }

        // Create a new favorite entry
        $favoris = new Favoris();
        $favoris->setProduit($produit);
        $favoris->setUserId($userId);

        $em->persist($favoris);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Produit ajouté aux favoris',
            'notification' => 'success'
        ]);
    } catch (\Exception $e) {
        return new JsonResponse([
            'error' => 'Une erreur est survenue.',
            'details' => $e->getMessage(),
            'notification' => 'error'
        ], 500);
    }
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

    #[Route('/remove/{id}', name: 'remove_from_favorites', methods: ['POST'])]
    public function removeFromFavorites(int $id, EntityManagerInterface $em, Request $request): Response
    {
        // Validate CSRF token
        if (!$this->isCsrfTokenValid('remove' . $id, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('favoris_list');
        }
    
        try {
            $favoris = $em->getRepository(Favoris::class)->findOneBy(['produit' => $id]);
    
            if (!$favoris) {
                $this->addFlash('warning', 'Produit non trouvé dans les favoris.');
                return $this->redirectToRoute('favoris_list');
            }
    
            $em->remove($favoris);
            $em->flush();
    
            $this->addFlash('success', 'Produit retiré des favoris.');
            return $this->redirectToRoute('favoris_list');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur lors de la suppression.');
            return $this->redirectToRoute('favoris_list');
        }
    }

    #[Route('/favoris/dashboard', name: 'favoris_dashboard')]
public function favorisDashboard(FavorisRepository $favorisRepo): Response
{
    $userId = 1; // Example: Static user ID
    $favorisList = $favorisRepo->findFavoritesByUser($userId);

    return $this->render('produit/favoris_dashboard.html.twig', [
        'favorisList' => $favorisList,
    ]);
}
#[Route('/favorites/count', name: 'favorites_count', methods: ['GET'])]
public function countFavorites(SessionInterface $session): JsonResponse
{
    $favorites = $session->get('favorites', []);
    return new JsonResponse(['count' => count($favorites)]);
}


}
