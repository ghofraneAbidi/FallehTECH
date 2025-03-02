<?php

// src/Controller/TaskController.php
namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tasks')]
class TaskController extends AbstractController
{
    #[Route('/add', name: 'add_task', methods: ['POST'])]
    public function addTask(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $task = new Task();
        $task->setTitle($data['title']);
        $task->setStartDate(new \DateTime($data['start']));
        $task->setEndDate(new \DateTime($data['end']));

        $em->persist($task);
        $em->flush();

        return $this->json([
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'start' => $task->getStartDate()->format('Y-m-d'),
            'end' => $task->getEndDate()->format('Y-m-d'),
        ]);
    }

    #[Route('/list', name: 'list_tasks', methods: ['GET'])]
    public function listTasks(TaskRepository $taskRepository): JsonResponse
    {
        $tasks = $taskRepository->findAll();
        $formattedTasks = array_map(fn ($task) => [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'start' => $task->getStartDate()->format('Y-m-d'),
            'end' => $task->getEndDate()->format('Y-m-d'),
        ], $tasks);

        return $this->json($formattedTasks);
    }
}
