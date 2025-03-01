<?php

namespace App\Controller;

use App\Entity\Land;
use App\Repository\LandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/land')]
class LandController extends AbstractController
{
    #[Route('/create', name: 'create_land', methods: ['POST'])]
    public function createLand(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['coordinates'])) {
            return new JsonResponse(['error' => 'Missing fields'], 400);
        }

        $land = new Land();
        $land->setOwner($this->getUser());
        $land->setName($data['name']);
        $land->setDescription($data['description'] ?? null);
        
        // Convert coordinates array to POLYGON format
        $polygonString = "POLYGON((";
        foreach ($data['coordinates'] as $point) {
            $polygonString .= "{$point[0]} {$point[1]},";
        }
        $polygonString = rtrim($polygonString, ',') . "))";

        $land->setCoordinates($polygonString);

        $entityManager->persist($land);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Land created successfully'], 201);
    }

    #[Route('/list', name: 'list_lands', methods: ['GET'])]
    public function listLands(LandRepository $landRepository): JsonResponse
    {
        $lands = $landRepository->findAll();
        $data = [];

        foreach ($lands as $land) {
            preg_match_all('/([\d\.]+) ([\d\.]+)/', $land->getCoordinates(), $matches);
            $coordinates = array_map(null, $matches[1], $matches[2]);

            $data[] = [
                'id' => $land->getId(),
                'name' => $land->getName(),
                'description' => $land->getDescription(),
                'coordinates' => $coordinates
            ];
        }

        return new JsonResponse($data);
    }
}
