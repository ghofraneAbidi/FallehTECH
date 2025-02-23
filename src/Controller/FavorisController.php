<?php

namespace App\Controller;

use App\Entity\Favoris;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FavorisRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class FavorisController extends AbstractController
{
    #[Route('/favoris/add/{id}', name: 'favoris_add', methods: ['POST'])]
    public function addToFavorites(Produit $produit, EntityManagerInterface $em): JsonResponse
    {
        try {
            // Static user instance (Hardcoded user ID)
            $user = $em->getRepository(User::class)->find(1);
            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Utilisateur introuvable.'
                ], 404);
            }

            // Check if the product is already in favorites
            $existingFavoris = $em->getRepository(Favoris::class)->findOneBy([
                'produit' => $produit,
                'user' => $user,
            ]);

            if ($existingFavoris) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Produit déjà dans les favoris'
                ]);
            }

            // Add the product to favorites
            $favoris = new Favoris();
            $favoris->setProduit($produit);
            $favoris->setUser($user);

            $em->persist($favoris);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Produit ajouté aux favoris'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Get all favorites for the static user
     */
    #[Route('/favoris/list', name: 'favoris_list')]
    public function showFavoris(FavorisRepository $favorisRepo, EntityManagerInterface $em): Response
    {
        // Static user instance (Hardcoded user ID)
        $user = $em->getRepository(User::class)->find(1);
        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        // Fetch favoris for the static user
        $favorisList = $favorisRepo->findBy(['user' => $user]);

        // Render the view and pass the favoris list to Twig
        return $this->render('favoris/list.html.twig', [
            'favorisList' => $favorisList,
        ]);
    }

    /**
     * ✅ Remove a product from favorites
     */
    #[Route('/favoris/remove/{id}', name: 'remove_from_favorites', methods: ['POST'])]
    public function removeFromFavorites(int $id, EntityManagerInterface $em, Request $request): Response
    {
        // Validate CSRF token
        if (!$this->isCsrfTokenValid('remove' . $id, $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('favoris_list');
        }

        try {
            // Static user instance (Hardcoded user ID)
            $user = $em->getRepository(User::class)->find(1);
            if (!$user) {
                throw $this->createNotFoundException("Utilisateur introuvable.");
            }

            // Find the favorite entry
            $favoris = $em->getRepository(Favoris::class)->findOneBy([
                'produit' => $id,
                'user' => $user,
            ]);

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

    /**
     * ✅ Dashboard for user's favorites
     */
    #[Route('/favoris/dashboard', name: 'favoris_dashboard')]
    public function favorisDashboard(FavorisRepository $favorisRepo, EntityManagerInterface $em): Response
    {
        $user = $em->getRepository(User::class)->find(1);
        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable.");
        }

        $favorisList = $favorisRepo->findBy(['user' => $user]);

        return $this->render('produit/favoris_dashboard.html.twig', [
            'favorisList' => $favorisList,
        ]);
    }

    /**
     * ✅ Get count of favorites for UI display
     */
    #[Route('/favorites/count', name: 'favorites_count', methods: ['GET'])]
    public function countFavorites(FavorisRepository $favorisRepo, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find(1);
        if (!$user) {
            return new JsonResponse(['count' => 0]);
        }

        $count = $favorisRepo->count(['user' => $user]);

        return new JsonResponse(['count' => $count]);
    }
}
