<?php

// src/Controller/TaskController.php
namespace App\Controller;
use App\Entity\Task;
use App\Entity\Utilisateur;

use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/tasks')]
class TaskController extends AbstractController
{
    #[Route('/list', name: 'app_tasks_list', methods: ['GET'])]
    public function listTasks(EntityManagerInterface $entityManager, SessionInterface $session, LoggerInterface $logger): JsonResponse
    {
        // ✅ Get the authenticated user
        $userId = $session->get('impersonated_user_id');
        if (!$userId) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $entityManager->getRepository(Utilisateur::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // ✅ Fetch tasks related to the user
        $tasks = $entityManager->getRepository(Task::class)->findBy(['user' => $user]);

        $taskList = [];
        foreach ($tasks as $task) {
            $taskList[] = [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'start' => $task->getStartDate()->format('Y-m-d'),
                'end' => $task->getEndDate()->format('Y-m-d'),
            ];
        }

        return new JsonResponse($taskList);
    }

    #[Route('/add', name: 'app_tasks_add', methods: ['POST'])]
    public function addTask(Request $request, EntityManagerInterface $entityManager, SessionInterface $session, LoggerInterface $logger): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title'], $data['start'], $data['end'])) {
            return new JsonResponse(['error' => 'Invalid data'], Response::HTTP_BAD_REQUEST);
        }

        // ✅ Get the authenticated user
        $userId = $session->get('impersonated_user_id');
        if (!$userId) {
            return new JsonResponse(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $user = $entityManager->getRepository(Utilisateur::class)->find($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // ✅ Create new task
        $task = new Task();
        $task->setTitle($data['title']);
        $task->setStartDate(new \DateTime($data['start']));
        $task->setEndDate(new \DateTime($data['end']));
        $task->setUser($user);

        // ✅ Save task to database
        $entityManager->persist($task);
        $entityManager->flush();

        return new JsonResponse([
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'start' => $task->getStartDate()->format('Y-m-d'),
            'end' => $task->getEndDate()->format('Y-m-d'),
        ]);
    }
}
