<?php
namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Post;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    private $entityManager;
    private $notificationRepository;

    public function __construct(EntityManagerInterface $entityManager, NotificationRepository $notificationRepository)
    {
        $this->entityManager = $entityManager;
        $this->notificationRepository = $notificationRepository;
    }

    public function createNotification(User $user, string $type, string $message, ?Post $post = null): void
    {
        // Créer la notification
    $notification = new Notification();
    $notification->setUser($user)
                 ->setMessage($message)
                 ->setPost($post); // Vous pouvez lier la notification à un post spécifique si nécessaire

    // Sauvegarder la notification dans la base de données
    $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
    
}
