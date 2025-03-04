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
use App\Entity\OffreEmploi;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[Route('/land')]
class LandController extends AbstractController
{

    #[Route('/create', name: 'create_land', methods: ['POST'])]
    public function createLand(Request $request, EntityManagerInterface $entityManager, SessionInterface $session): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
    
            // ✅ Debugging: Log received data
            error_log("📥 Received Data: " . json_encode($data));
    
            if (!$data || !isset($data['name']) || !isset($data['coordinates']) || !is_array($data['coordinates'])) {
                error_log("❌ Invalid data format received.");
                return new JsonResponse(['error' => 'Missing or invalid fields'], 400);
            }
    
            // ✅ Fetch Impersonated User from Session
            $impersonatedUserId = $session->get('impersonated_user_id');
            if (!$impersonatedUserId) {
                error_log("❌ Error: User not impersonated. Session variable missing.");
                return new JsonResponse(['error' => 'User not impersonated'], 403);
            }
    
            $owner = $entityManager->getRepository(Utilisateur::class)->find($impersonatedUserId);
            if (!$owner) {
                error_log("❌ Error: Impersonated user with ID $impersonatedUserId not found.");
                return new JsonResponse(['error' => 'User not found in database'], 404);
            }
    
            // ✅ Creating Land entity
            $land = new Land();
            $land->setName($data['name']);
            $land->setArea(floatval($data['area'] ?? 0));
            $land->setOwner($owner); // ✅ Assign the impersonated user as the owner
    
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
    
            error_log("✅ Land saved successfully with ID: " . $land->getId() . " and owner ID: " . $owner->getId());
    
            return new JsonResponse([
                'message' => 'Land created successfully',
                'landId' => $land->getId(),
                'ownerId' => $owner->getId()
            ], 201);
    
        } catch (\Exception $e) {
            error_log("❌ Server Error: " . $e->getMessage()); // Log the error
            return new JsonResponse(['error' => 'Internal Server Error'], 500);
        }
    }
    
    
    #[Route('/list', name: 'list_lands', methods: ['GET'])]
    public function listLands(LandRepository $landRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $lands = $landRepository->findAll();
            $data = [];
    
            foreach ($lands as $land) {
                $owner = $land->getOwner();
                if (!$owner) {
                    error_log("❌ Error: Land ID " . $land->getId() . " has no owner!");
                    continue;
                }
    
                // ✅ Fetch offers using the employer ID
                $offers = $entityManager->getRepository(OffreEmploi::class)->findBy(['id_employeur' => $owner]);
    
                // ✅ Format offers
                $formattedOffers = [];
                foreach ($offers as $offer) {
                    $formattedOffers[] = [
                        'id' => $offer->getId(),
                        'title' => $offer->getTitre(),
                        'description' => $offer->getDescription(),
                        'salaire' => $offer->getSalaire(),
                    ];
                }
    
                // ✅ Get land coordinates
                $points = [];
                foreach ($land->getPoints() as $point) {
                    $points[] = [
                        'latitude' => $point->getLatitude(),
                        'longitude' => $point->getLongitude(),
                    ];
                }
    
                // ✅ Ensure all required data is returned
                $data[] = [
                    'id' => $land->getId(),
                    'name' => $land->getName(),
                    'description' => $land->getDescription(),
                    'area' => $land->getArea(),
                    'coordinates' => $points,
                    'owner' => $owner->getNom(), // ✅ Ensure owner is returned
                    'offers' => $formattedOffers, // ✅ Ensure offers are included
                ];
            }
    
            error_log("✅ Successfully fetched lands");
            return new JsonResponse($data, 200, ['Content-Type' => 'application/json']);
    
        } catch (\Exception $e) {
            error_log("❌ Error fetching lands: " . $e->getMessage());
            return new JsonResponse(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }
   

   
    #[Route('/worker/map', name: 'worker_map')]
   public function workerMap(): Response
   {
       return $this->render('map/map_worker.html.twig');
   }

   private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/land/profile/{id}', name: 'land_profile')]
    public function landProfile($id, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): Response
    {
        $land = $entityManager->getRepository(Land::class)->find($id);
    
        if (!$land) {
            throw $this->createNotFoundException('Land not found');
        }
    
        // Convert Points to an array of coordinates
        $coordinates = [];
        foreach ($land->getPoints() as $point) {
            $coordinates[] = [
                'latitude' => $point->getLatitude(),
                'longitude' => $point->getLongitude()
            ];
        }
    
        // Get the land owner and offers
        $owner = $land->getOwner();
        $offers = $entityManager->getRepository(OffreEmploi::class)->findBy(['id_employeur' => $owner]);
    
        // AI Estimation using DeepAI API
        $apiKey = "02ca0d9e-a84b-4583-a1f0-ec3dc525f757"; // Replace with your free API key from DeepAI
        $area = $land->getArea();
        $landDescription = $land->getDescription() ?: "No description provided";
        try {
            $response = $httpClient->request('POST', 'http://localhost:1234/v1/chat/completions', [
                'json' => [
                    'model' => 'your_model_name_here', // Replace with your model name
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are an expert in agriculture.'],
                        ['role' => 'user', 'content' => "Estimate workforce & cost for $area m² land: '$landDescription'."]
                    ]
                ]
            ]);
            
            // Log Raw API Response
            $statusCode = $response->getStatusCode();
            $rawResponse = $response->getContent(false);
            error_log("DeepAI API Status Code: " . $statusCode);
            error_log("DeepAI Raw Response: " . $rawResponse);
        
            // Convert JSON Response
            $data = json_decode($rawResponse, true);
        
            if ($statusCode !== 200) {
                throw new \Exception("API Error: HTTP $statusCode - " . ($data['error'] ?? "Unknown error"));
            }
        
            if (!isset($data['output'])) {
                throw new \Exception("Invalid API response format. Response: " . $rawResponse);
            }
        
            $workDescription = $data['output'];
            $estimatedCost = "Estimated cost: " . rand(5000, 15000) . " TND"; // Mock cost
        
        } catch (\Exception $e) {
            error_log("⚠️ AI API Error: " . $e->getMessage());
        
            // **Display a helpful error message in the UI**
            if (strpos($e->getMessage(), 'API Error: HTTP 403') !== false) {
                $workDescription = "❌ API key invalid or expired. Please check your DeepAI API key.";
            } elseif (strpos($e->getMessage(), 'API Error: HTTP 429') !== false) {
                $workDescription = "⚠️ Too many requests. Try again later.";
            } elseif (strpos($e->getMessage(), 'API Error: HTTP 500') !== false) {
                $workDescription = "🚨 DeepAI server issue. Try again later.";
            } elseif (strpos($e->getMessage(), 'Invalid API response format') !== false) {
                $workDescription = "❌ AI failed to generate an estimation. Response format issue.";
            } else {
                $workDescription = "⚠️ AI estimation failed: " . $e->getMessage();
            }
        
            $estimatedCost = "⚠️ AI estimation failed.";
        }
        
        
        return $this->render('map/profile.html.twig', [
            'land' => $land,
            'coordinates' => $coordinates,
            'offers' => $offers,
            'workDescription' => $workDescription,
            'estimatedCost' => $estimatedCost
        ]);
    }
}
