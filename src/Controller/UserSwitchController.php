<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\Utilisateur;

class UserSwitchController extends AbstractController
{
    #[Route('/switch-user/{id}', name: 'app_switch_user')]
    public function switchUser(int $id, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $user = $entityManager->getRepository(Utilisateur::class)->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException("User with ID $id not found.");
        }
    
        // Set the user ID in the session for impersonation
        $session->set('impersonated_user_id', $user->getId());
    
        $this->addFlash('success', "You are now impersonating {$user->getNom()}");
    
         if ($user->getRole() === "admin") {
            return $this->redirectToRoute('app_candidature_index');
        } elseif ($user->getRole() === "ouvrier") {
            return $this->redirectToRoute('app_ouvrier_offers');
        } elseif ($user->getRole() === "agriculteur") {
            return $this->redirectToRoute('app_offre_emploi_index');
        }
    
        // Default redirect if no role matches
        return $this->redirectToRoute('app_switch_user_page');
    }

    #[Route('/switch-user', name: 'app_switch_user_page')]
    public function switchUserPage(EntityManagerInterface $entityManager, Security $security): Response
    {
        return $this->render('log.html.twig'); // No need to pass users here, we fetch them dynamically
    }

    #[Route('/fetch-users-by-role', name: 'app_fetch_users_by_role', methods: ['GET'])]
    public function fetchUsersByRole(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $role = $request->query->get('role');
    
        if (!$role) {
            return new JsonResponse(['error' => 'Role is required'], Response::HTTP_BAD_REQUEST);
        }
    
        // Fetch users with the given role
        $users = $entityManager->getRepository(Utilisateur::class)->findBy(['role' => $role]);
    
        if (!$users) {
            return new JsonResponse([]);
        }
    
        $userList = [];
        foreach ($users as $user) {
            $userList[] = [
                'id' => $user->getId(),
                'nom' => $user->getNom(),
                'role' => $user->getRole(),
                'path' => $this->generateUrl('app_switch_user', ['id' => $user->getId()])
            ];
        }
    
        return new JsonResponse($userList);
    }
    
    #[Route('/admin', name: 'admin_dashboard')]
    public function adminDashboard(): Response {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/worker', name: 'worker_dashboard')]
    public function workerDashboard(): Response {
        return $this->render('worker/dashboard.html.twig');
    }

    #[Route('/client', name: 'client_dashboard')]
    public function clientDashboard(): Response {
        return $this->render('client/dashboard.html.twig');
    }
}
