<?php

namespace App\Controller;

use App\Entity\Like;
use App\Form\LikeType;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\NotificationService;  

#[Route('/like')]
class LikeController extends AbstractController
{
    private $notificationService;
     // Injection du service de notification
     public function __construct(NotificationService $notificationService)
     {
         $this->notificationService = $notificationService;
     }

    #[Route('/', name: 'app_like_index', methods: ['GET'])]
    public function index(LikeRepository $likeRepository): Response
    {
        return $this->render('like/index.html.twig', [
            'likes' => $likeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_like_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $like = new Like();
        $form = $this->createForm(LikeType::class, $like);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($like);
            $entityManager->flush();

            $post = $like->getPost();
            // Vérifier si un post est lié et envoyer la notification
            if ($post) {
                // Récupérer l'admin du post (l'utilisateur qui a créé le post)
                $admin = $post->getUser(); // Remplacer par la logique appropriée
                $user = $this->getUser(); // L'utilisateur qui a ajouté le like

                // Envoyer une notification à l'admin
                $this->notificationService->createNotification(
                    $admin,
                    'like',
                    "{$user} a commenté votre post.",
                    $post
                );
            }

            return $this->redirectToRoute('app_like_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('like/new.html.twig', [
            'like' => $like,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_like_show', methods: ['GET'])]
    public function show(Like $like): Response
    {
        return $this->render('like/show.html.twig', [
            'like' => $like,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_like_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Like $like, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(LikeType::class, $like);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_like_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('like/edit.html.twig', [
            'like' => $like,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_like_delete', methods: ['POST'])]
    public function delete(Request $request, Like $like, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$like->getId(), $request->request->get('_token'))) {
            $entityManager->remove($like);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_like_index', [], Response::HTTP_SEE_OTHER);
    }

    public function likePost(Post $post): Response
{
    $user = $this->getUser(); // L'utilisateur qui aime le post

    // Créer une notification pour le propriétaire du post
    $message = "{$user->getUsername()} a aimé votre post: {$post->getTitle()}";
    $this->notificationService->createNotification(
        $post->getUser(), // Le propriétaire du post
        'like', // Type de notification
        $message, // Le message
        $post // Optionnel: lier la notification à un post
    );

    // Autres logiques pour enregistrer le "like"
    // ...

    return $this->redirectToRoute('post_show', ['id' => $post->getId()]);
}
}
