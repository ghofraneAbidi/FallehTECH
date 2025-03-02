<?php

namespace App\Controller;

use App\Entity\Land;
use App\Entity\Point;
use App\Repository\LandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Utilisateur;

#[Route('/land')]
class LandController extends AbstractController
{

    #[Route('/create', name: 'create_land', methods: ['POST'])]
    public function createLand(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
    
            // ✅ Debugging: Log received data
            error_log("📥 Received Data: " . json_encode($data));
    
            if (!$data || !isset($data['name']) || !isset($data['coordinates']) || !is_array($data['coordinates'])) {
                error_log("❌ Invalid data format received.");
                return new JsonResponse(['error' => 'Missing or invalid fields'], 400);
            }
    
            // ✅ Fetch Static Owner (Utilisateur with id = 2)
            $owner = $entityManager->getRepository(Utilisateur::class)->find(2);
            if (!$owner) {
                error_log("❌ Error: Owner with ID 2 not found.");
                return new JsonResponse(['error' => 'Static owner not found'], 500);
            }
    
            // ✅ Creating Land entity
            $land = new Land();
            $land->setName($data['name']);
            $land->setArea(floatval($data['area'] ?? 0));
            $land->setOwner($owner); // Setting static owner
    
            // ✅ Ensure coordinates are valid
            if (empty($data['coordinates']) || !is_array($data['coordinates'])) {
                error_log("❌ Coordinates missing or invalid");
                return new JsonResponse(['error' => 'Invalid coordinates'], 400);
            }
    
            foreach ($data['coordinates'] as $pointData) {
                if (!isset($pointData['latitude']) || !isset($pointData['longitude'])) {
                    error_log("❌ Invalid point data: " . json_encode($pointData));
                    return new JsonResponse(['error' => 'Invalid point data'], 400);
                }
    
                $point = new Point();
                $point->setLatitude($pointData['latitude']);
                $point->setLongitude($pointData['longitude']);
                $point->setLand($land);
                $entityManager->persist($point);
            }
    
            // ✅ Persist land
            $entityManager->persist($land);
            $entityManager->flush();
    
            error_log("✅ Land saved successfully with ID: " . $land->getId());
    
            return new JsonResponse([
                'message' => 'Land created successfully',
                'landId' => $land->getId()
            ], 201);
    
        } catch (\Exception $e) {
            error_log("❌ Server Error: " . $e->getMessage()); // Log the error
            return new JsonResponse(['error' => 'Internal Server Error'], 500);
        }
    }
    
    


    #[Route('/list', name: 'list_lands', methods: ['GET'])]
    public function listLands(LandRepository $landRepository): JsonResponse
    {
        $lands = $landRepository->findAll();
        $data = [];

        foreach ($lands as $land) {
            $points = [];
            foreach ($land->getPoints() as $point) {
                $points[] = [
                    'latitude' => $point->getLatitude(),
                    'longitude' => $point->getLongitude(),
                ];
            }

            $data[] = [
                'id' => $land->getId(),
                'name' => $land->getName(),
                'description' => $land->getDescription(),
                'area' => $land->getArea(),
                'coordinates' => $points,
            ];
        }

        return new JsonResponse($data);
    }
}
