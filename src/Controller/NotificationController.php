<?php
namespace App\Controller;

use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'notifications_list', methods: ['GET'])]
    public function getNotifications(NotificationRepository $notificationRepository, UserInterface $user): JsonResponse
    {
        $notifications = $notificationRepository->findAll();

        $data = [];
        foreach ($notifications as $notification) {
            $data[] = [
                'id' => $notification->id,
                'message' => $notification->getMessage(),
                'createdAt' => $notification->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
    }
}
