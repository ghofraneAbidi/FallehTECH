<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Conversation;
use App\Entity\Utilisateur;
use App\Repository\MessageRepository;
use App\Repository\ConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/messaging')]
class MessagingController extends AbstractController
{
    #[Route('/', name: 'messenger', methods: ['GET'])]
    public function index(EntityManagerInterface $em, SessionInterface $session): Response
    {
        // Retrieve impersonated user from session
        $impersonatedUserId = $session->get('impersonated_user_id');
        if (!$impersonatedUserId) {
            return new Response("No impersonated user found. Please switch to a user first.", 403);
        }
    
        $user = $em->getRepository(Utilisateur::class)->find($impersonatedUserId);
        if (!$user) {
            return new Response("User not found.", 404);
        }
    
        // Get all users except the impersonated sender
        $users = $em->getRepository(Utilisateur::class)->findAll();
        $users = array_filter($users, fn($u) => $u->getId() !== $user->getId());
    
        // Fetch unread messages per user
        $unreadMessages = $em->createQuery("
            SELECT IDENTITY(m.sender) as senderId
            FROM App\Entity\Message m
            WHERE m.receiver = :user AND m.isRead = false
            GROUP BY m.sender
        ")
        ->setParameter('user', $user)
        ->getResult();
    
        // Convert unread messages to an array of sender IDs
        $unreadSenderIds = array_map(fn($msg) => $msg['senderId'], $unreadMessages);
    
        return $this->render('worker/mess.html.twig', [
            'users' => $users,
            'testUser' => $user,
            'unreadMessages' => $unreadSenderIds, // Pass unread messages to Twig
        ]);
    }
    

    #[Route('/send', name: 'send_message', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        EntityManagerInterface $em,
        ConversationRepository $conversationRepo,
        SessionInterface $session
    ): JsonResponse {
        // Retrieve impersonated user
        $impersonatedUserId = $session->get('impersonated_user_id');
        if (!$impersonatedUserId) {
            return new JsonResponse(['error' => 'No impersonated user found. Please switch to a user first.'], 403);
        }

        $user = $em->getRepository(Utilisateur::class)->find($impersonatedUserId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found.'], 404);
        }

        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['recipient_id'], $data['content']) || empty(trim($data['content']))) {
            return new JsonResponse(['error' => 'Invalid input data'], 400);
        }

        $recipient = $em->getRepository(Utilisateur::class)->find($data['recipient_id']);
        if (!$recipient) {
            return new JsonResponse(['error' => 'Recipient not found'], 404);
        }

        // Find or create conversation
        $conversation = $conversationRepo->findOneBy([
            'user1' => $user,
            'user2' => $recipient
        ]) ?? $conversationRepo->findOneBy([
            'user1' => $recipient,
            'user2' => $user
        ]);

        if (!$conversation) {
            $conversation = new Conversation();
            $conversation->setUser1($user);
            $conversation->setUser2($recipient);
            $em->persist($conversation);
        }

        // Create a new message with isRead explicitly set to false
        $message = new Message();
        $message->setSender($user);
        $message->setReceiver($recipient);
        $message->setConversation($conversation);
        $message->setContent($data['content']);
        $message->setIsRead(false);

        $em->persist($message);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Message sent successfully',
            'messageData' => [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()->format('H:i'),
                'sender' => [
                    'id' => $message->getSender()->getId(),
                    'name' => $message->getSender()->getNom()
                ],
                'receiver' => [
                    'id' => $message->getReceiver()->getId(),
                    'name' => $message->getReceiver()->getNom()
                ]
            ]
        ]);
    }

    #[Route('/conversation/{id}', name: 'get_conversation', methods: ['GET'])]
    public function getConversation(int $id, MessageRepository $messageRepo, EntityManagerInterface $em, SessionInterface $session): JsonResponse
    {
        // Retrieve impersonated user
        $impersonatedUserId = $session->get('impersonated_user_id');
        if (!$impersonatedUserId) {
            return new JsonResponse(['error' => 'No impersonated user found. Please switch to a user first.'], 403);
        }

        $user = $em->getRepository(Utilisateur::class)->find($impersonatedUserId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found.'], 404);
        }

        $receiver = $em->getRepository(Utilisateur::class)->find($id);
        if (!$receiver) {
            return new JsonResponse(['error' => 'Receiver not found.'], 404);
        }

        $messages = $messageRepo->createQueryBuilder('m')
            ->where('(m.sender = :user AND m.receiver = :receiver) OR (m.sender = :receiver AND m.receiver = :user)')
            ->setParameter('user', $user->getId())
            ->setParameter('receiver', $receiver->getId())
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();

        $messagesData = array_map(fn($m) => [
            'id' => $m->getId(),
            'content' => $m->getContent(),
            'createdAt' => $m->getCreatedAt()->format('H:i'),
            'sender' => ['id' => $m->getSender()->getId(), 'name' => $m->getSender()->getNom()]
        ], $messages);

        return new JsonResponse($messagesData);
    }
    #[Route('/notifications/unread', name: 'unread_notifications', methods: ['GET'])]
    public function getUnreadMessages(EntityManagerInterface $em, SessionInterface $session): JsonResponse
    {
        $impersonatedUserId = $session->get('impersonated_user_id');
        if (!$impersonatedUserId) {
            return new JsonResponse(['error' => 'No user session found'], 403);
        }
    
        $user = $em->getRepository(Utilisateur::class)->find($impersonatedUserId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }
    
        $unreadMessages = $em->createQuery("
            SELECT COUNT(m.id) as unreadCount, 
                   IDENTITY(m.sender) as senderId, 
                   u.nom as senderName
            FROM App\Entity\Message m
            JOIN App\Entity\Utilisateur u WITH u.id = m.sender
            WHERE m.receiver = :user AND m.isRead = false
            GROUP BY m.sender, u.nom
        ")
        ->setParameter('user', $user)
        ->getResult();
    
        return new JsonResponse($unreadMessages);
    }
    
    
    
    #[Route('/conversation/{id}/read', name: 'mark_messages_read', methods: ['POST'])]
    public function markMessagesAsRead(int $id, EntityManagerInterface $em, SessionInterface $session): JsonResponse
    {
        // Get impersonated user
        $impersonatedUserId = $session->get('impersonated_user_id');
        if (!$impersonatedUserId) {
            return new JsonResponse(['error' => 'No user session found'], 403);
        }
    
        $receiver = $em->getRepository(Utilisateur::class)->find($impersonatedUserId);
        if (!$receiver) {
            return new JsonResponse(['error' => 'Receiver (current user) not found'], 404);
        }
    
        $sender = $em->getRepository(Utilisateur::class)->find($id);
        if (!$sender) {
            return new JsonResponse(['error' => 'Sender not found'], 404);
        }
    
        // Update messages as read
        $query = $em->createQuery("
            UPDATE App\Entity\Message m
            SET m.isRead = true
            WHERE m.sender = :sender AND m.receiver = :receiver AND m.isRead = false
        ")
        ->setParameter('sender', $sender)
        ->setParameter('receiver', $receiver)
        ->execute();
    
        return new JsonResponse(['success' => true, 'message' => 'Messages marked as read']);
    }
    


    
}
