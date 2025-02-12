<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\UserRepository;

final class BackOfficeController extends AbstractController
{
    #[Route('/back_office', name: 'app_back_office')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('backoffice/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);

    }
}
