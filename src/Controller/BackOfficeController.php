<?php

// src/Controller/BackOfficeController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;

final class BackOfficeController extends AbstractController
{
    #[Route('/back_office', name: 'app_back_office')]
    public function index(UserRepository $userRepository): Response
    {
        // Fetch all users sorted alphabetically by name
        $users = $userRepository->findBy([], ['name' => 'ASC']);

        // Initialize an array to store the count of users by role
        $usersByRole = [];

        // Calculate the number of users by role
        foreach ($users as $user) {
            $role = $user->getRole();
            if ($role != "ROLE_ADMIN"){
                // Ensure we have only unique roles and count them
                $usersByRole[$role] = ($usersByRole[$role] ?? 0) + 1;
            
            }
        }

        return $this->render('backoffice/index.html.twig', [
            'users' => $users,
            'usersByRole' => $usersByRole,
        ]);
    }

    #[Route('/search_users', name: 'search_users', methods: ['GET'])]
    public function searchUsers(Request $request, UserRepository $userRepository): JsonResponse
    {
        $searchTerm = $request->query->get('email', '');

        if (!$searchTerm) {
            return new JsonResponse([]);
        }

        $users = $userRepository->createQueryBuilder('u')
            ->where('u.email LIKE :email')
            ->setParameter('email', "%$searchTerm%")
            ->orderBy('u.name', 'ASC')
            ->getQuery()
            ->getResult();

        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'role' => $user->getRoles()[0] ?? 'N/A',
            ];
        }

        return new JsonResponse($data);
    }
}
