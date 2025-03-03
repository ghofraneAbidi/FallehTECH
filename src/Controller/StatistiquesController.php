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
public function index(): Response
{
    return $this->render('stats/index.html.twig', []);
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
}
