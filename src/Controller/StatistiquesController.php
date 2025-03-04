<?php
namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class StatistiquesController extends AbstractController
{
    #[Route('/stats', name: 'app_stats')]
public function index(ProduitRepository $produitRepository): Response
{
    $lowStockProducts = $produitRepository->createQueryBuilder('p')
        ->where('p.stock < 5')
        ->getQuery()
        ->getResult();
    $produits = $produitRepository->findAll(); // Récupère tous les produits

    return $this->render('stats/index.html.twig', [
        'produits' => $produits ,// Envoie la liste des produits à la vue
        'lowStockProducts' => $lowStockProducts
    ]);
}



    #[Route('/stats/data', name: 'app_stats_data', methods: ['GET'])]
    public function getStatsData(ProduitRepository $produitRepository, Request $request): JsonResponse
    {
        // Récupérer le type de statistique demandé (par défaut: "bar")
        $type = $request->query->get('type', 'bar');

        // Exemples de statistiques :
        $produitsParCategorie = $produitRepository->countByCategorie();

        return new JsonResponse([
            'type' => $type,
            'produitsParCategorie' => $produitsParCategorie,
        ]);
    }

    #[Route('/stats/stock-evolution/{id}', name: 'app_stock_evolution', methods: ['GET'])]
public function stockEvolution(int $id, ProduitRepository $produitRepository): JsonResponse
{
    $stockEvolution = $produitRepository->getStockEvolution($id);

    return new JsonResponse($stockEvolution);
}
#[Route('/stats/low-stock', name: 'app_stats_low_stock')]
    public function lowStock(ProduitRepository $produitRepository): JsonResponse
    {
        $produits = $produitRepository->findLowStock();

        $data = [];
        foreach ($produits as $produit) {
            $data[] = [
                'nom' => $produit->getNom(),
                'stock' => $produit->getStock(),
            ];
        }

        return new JsonResponse($data);
    }

}
