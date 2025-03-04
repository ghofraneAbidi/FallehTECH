<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Like;
use App\Service\NotificationService;  
use App\Form\PostType;
use App\Form\CommentType;
use App\Repository\PostRepository;
use App\Repository\LikeRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;   


#[Route('/post')]
class PostController extends AbstractController
{
    private $notificationService;
    // Injection du service de notification
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/statistics', name: 'app_post_statistics', methods: ['GET'])]
    public function statisticsByCategories(PostRepository $postRepository): Response
    {
        // Récupérer tous les posts
        $posts = $postRepository->findAll();

        // Initialiser un tableau pour stocker les statistiques par catégorie
        $categoryStats = [];

        // Compter le nombre de posts par catégorie
        foreach ($posts as $post) {
            $category = $post->getCategory();
            if (!isset($categoryStats[$category])) {
                $categoryStats[$category] = 0;
            }
            $categoryStats[$category]++;
        }

        // Envoyer les statistiques à la vue
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'categoryStats' => $categoryStats,
        ]);
    }


    #[IsGranted('ROLE_ADMIN')] 
    #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        // Récupérer tous les posts depuis la base de données
        $posts = $postRepository->findAll();
    
        // Générer les statistiques par catégorie
        $categoryStats = [];
        foreach ($posts as $post) {
            $category = $post->getCategory();
            if (!isset($categoryStats[$category])) {
                $categoryStats[$category] = 0;
            }
            $categoryStats[$category]++;
        } 
    
        // Renvoyer la vue avec les posts et les statistiques par catégorie
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'categoryStats' => $categoryStats,
        ]);
    }
    #[Route('/search-ajax', name: 'app_post_search_ajax', methods: ['GET'])]
public function searchAjax(Request $request, PostRepository $postRepository): JsonResponse
{
    $query = $request->query->get('q', '');

    // Recherche de posts basés sur le titre, la catégorie ou le contenu
    $posts = $postRepository->createQueryBuilder('p')
        ->where('p.titre LIKE :query')
        ->orWhere('p.category LIKE :query')
        ->orWhere('p.contenu LIKE :query')
        ->setParameter('query', '%' . $query . '%')
        ->getQuery()
        ->getResult();

    // Construire un tableau JSON avec les résultats
    $data = [];
    foreach ($posts as $post) {
        $data[] = [
            'id' => $post->getId(),
            'titre' => $post->getTitre(),
            'category' => $post->getCategory(),
            'contenu' => substr($post->getContenu(), 0, 50) . '...',
            'date' => $post->getDate() ? $post->getDate()->format('Y-m-d') : '-',
            'image' => $post->getImage()
        ];
    }

    return new JsonResponse($data);
}

   /* #[Route('/', name: 'app_post_index', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('post/index.html.twig', [
            'posts' => $postRepository->findAll(),
        ]);
    }

 */
   



   /* #[Route('/front', name: 'app_post_front', methods: ['GET'])]
    public function front(PostRepository $postRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $queryBuilder = $postRepository->createQueryBuilder('p');
    
        $pagination = $paginator->paginate(
            $queryBuilder, // query NOT result  
            $request->query->getInt('page', 1),// *page number 
            9 //limit per page 
        );
    
        return $this->render('post/index-front.html.twig', [
            'pagination' => $pagination,
        ]);
    }*/



   /* #[Route('/front', name: 'app_post_front', methods: ['GET'])]
    public function front(PostRepository $postRepository, Request $request, PaginatorInterface $paginator): Response
    {
        // Get all posts for pagination
        $queryBuilder = $postRepository->createQueryBuilder('p');

        $pagination = $paginator->paginate(
            $queryBuilder, // query NOT result 
            $request->query->getInt('page', 1), // page number 
            9 // limit per page 
        );

        // Get the top 3 posts sorted by likes and comments count
        $bestPosts = $postRepository->createQueryBuilder('p')
            ->leftJoin('p.likes', 'l')
            ->leftJoin('p.comments', 'c')
            ->groupBy('p.id')
            ->orderBy('COUNT(l.id) + COUNT(c.id)', 'DESC') // Sort by sum of likes and comments
            ->setMaxResults(3)
            ->getQuery()
            ->getResult();

        return $this->render('post/index-front.html.twig', [
            'pagination' => $pagination,
            'bestPosts' => $bestPosts, // Pass the best posts to the template
        ]);
    }*/
    #[Route('/front', name: 'app_post_front', methods: ['GET'])]
public function front(PostRepository $postRepository, Request $request, PaginatorInterface $paginator): Response
{
    $category = $request->query->get('category'); // Get the selected category from the request

    // Query for filtered posts if category is selected
    $queryBuilder = $postRepository->createQueryBuilder('p');
    if ($category) {
        $queryBuilder->where('p.category = :category')
                     ->setParameter('category', $category);
    }

    $pagination = $paginator->paginate(
        $queryBuilder,
        $request->query->getInt('page', 1),
        6
    );

    // Get the top 3 best posts
    $bestPosts = $postRepository->createQueryBuilder('p')
        ->leftJoin('p.likes', 'l')
        ->leftJoin('p.comments', 'c')
        ->groupBy('p.id')
        ->orderBy('COUNT(l.id) + COUNT(c.id)', 'DESC')
        ->setMaxResults(3)
        ->getQuery()
        ->getResult();

    // Get all unique categories for the dropdown filter
    $categories = $postRepository->createQueryBuilder('p')
        ->select('DISTINCT p.category')
        ->getQuery()
        ->getResult();

    return $this->render('post/index-front.html.twig', [
        'pagination' => $pagination,
        'bestPosts' => $bestPosts,
        'categories' => $categories,
        'selectedCategory' => $category
    ]);
}




    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Set current date
                $post->setDate(new \DateTime());

                // Set the user
                $post->setUser($this->getUser());

                /** @var UploadedFile|null $imageFile */
                $imageFile = $form->get('imageFile')->getData();

                if ($imageFile) {
                    $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                    try {
                        $imageFile->move(
                            $this->getParameter('post_images_directory'),
                            $newFilename
                        );
                        $post->setImage($newFilename);
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Failed to upload image: ' . $e->getMessage());
                        return $this->redirectToRoute('app_post_new');
                    }
                }

                $entityManager->persist($post);
                $entityManager->flush();

                $this->addFlash('success', 'Post added successfully!');
                return $this->redirectToRoute('app_post_index');
            } else {
                $this->addFlash('error', 'Form validation failed. Please check your input.');
            }
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front/{id}', name: 'app_post_show_front', methods: ['GET', 'POST'])]
    public function showFront(Post $post, CommentRepository $commentRepository, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Create a new comment entity
        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
    
        // Handle form submission
        $commentForm->handleRequest($request);
        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $comment->setDate(new \DateTime());
            $comment->setPost($post);
            $comment->setUser($this->getUser()); // Set the user
            $comment->setDate(new \DateTime());
    
            $entityManager->persist($comment);
            $entityManager->flush();
            
            // After saving the comment, send a notification to the post author
            $this->notificationService->createNotification(
                $post->getUser(), // The post author
                'comment', // Type of notification
                "{$this->getUser()->getName()} has commented on your post.", // Message
                $post // The post where the comment was made
            );
    
            $this->addFlash('success', 'Comment added successfully!');
            return $this->redirectToRoute('app_post_show_front', ['id' => $post->getId()]);
        }
    
        return $this->render('post/show-front.html.twig', [
            'post' => $post,
            'comments' => $commentRepository->findBy(['post' => $post]),
            'commentForm' => $commentForm->createView(),
        ]);
    }
    

    #[Route('/{id}', name: 'app_post_show', methods: ['GET'])]
    public function show(Post $post, CommentRepository $commentRepository): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $commentRepository->findBy(['post' => $post], ['date' => 'DESC']),
        ]);
    }

    
    #[Route('/{id}/like', name: 'app_post_like', methods: ['POST'])]
    public function like(Post $post, EntityManagerInterface $entityManager, Request $request): Response
    {
        $session = $request->getSession();
        $likedPosts = $session->get('liked_posts', []);

        // If already liked, remove the like
        if (in_array($post->getId(), $likedPosts)) {
            $like = $entityManager->getRepository(Like::class)->findOneBy(['post' => $post, 'user' => $this->getUser()]);

            if ($like) {
                $entityManager->remove($like);
                $entityManager->flush();
            }

            // Remove from session
            $likedPosts = array_diff($likedPosts, [$post->getId()]);
            $session->set('liked_posts', $likedPosts);
            $admin = $post->getUser(); // Remplacer par la logique appropriée
            $user = $this->getUser(); // L'utilisateur qui a ajouté le like
 $this->notificationService->createNotification(
                    $admin,
                    'like',
                    "{$user} a aimé votre post.",
                    $post
                );

            $this->addFlash('success', 'You unliked this post.');
        } else {
            // Add new like
            $like = new Like();
            $like->setPost($post);
            $like->setUser($this->getUser());
            $entityManager->persist($like);
            $entityManager->flush();

            // Store in session to track likes
            $likedPosts[] = $post->getId();
            $session->set('liked_posts', $likedPosts);

            $this->addFlash('success', 'You liked this post.');
        }

        return $this->redirect($request->headers->get('referer'));
    }



    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_post_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('post_images_directory'), // Directory where images are stored
                        $newFilename
                    );
                    $post->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Failed to upload image: ' . $e->getMessage());
                    return $this->redirectToRoute('app_post_edit', ['id' => $post->getId()]);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Post updated successfully!');
            return $this->redirectToRoute('app_post_index');
        }

        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_post_delete', methods: ['POST'])]
    public function delete(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $post->getId(), $request->request->get('_token'))) {
            $entityManager->remove($post);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_post_index', [], Response::HTTP_SEE_OTHER);
    }







//---------------------
    #[Route('/comment/{id}/edit', name: 'app_comment_edit', methods: ['GET', 'POST'])]
    public function editComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if ($comment->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'You are not authorized to edit this comment.');
            return $this->redirectToRoute('app_post_show_front', ['id' => $comment->getPost()->getId()]);
        }

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Comment updated successfully!');
            return $this->redirectToRoute('app_post_show_front', ['id' => $comment->getPost()->getId()]);
        }
    
        return $this->render('post/edit_front.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/comment/{id}/delete', name: 'app_comment_delete', methods: ['POST'])]
    public function deleteComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        if ($comment->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'You are not authorized to delete this comment.');
            return $this->redirectToRoute('app_post_show_front', ['id' => $comment->getPost()->getId()]);
        }

        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Comment deleted successfully!');
        }
    
        return $this->redirectToRoute('app_post_show_front', ['id' => $comment->getPost()->getId()]);
    }

    


    #[Route('/statistics', name: 'app_post_statistics', methods: ['GET'])]
    public function overallStatistics(PostRepository $postRepository, CommentRepository $commentRepository, LikeRepository $likeRepository): Response
    {
        // Get all posts
        $posts = $postRepository->findAll();

        // Initialize an array to store post statistics
        $postStats = [];

        // Calculate statistics for each post
        foreach ($posts as $post) {
            $commentsCount = count($commentRepository->findBy(['post' => $post]));
            $likesCount = count($likeRepository->findBy(['post' => $post]));

            $postStats[] = [
                'post' => $post,
                'commentsCount' => $commentsCount,
                'likesCount' => $likesCount,
                'totalInteractions' => $commentsCount + $likesCount,
            ];
        }

        // Sort posts by total interactions (comments + likes) in descending order
        usort($postStats, function ($a, $b) {
            return $b['totalInteractions'] <=> $a['totalInteractions'];
        });

        // Get the top 10 posts
        $topPosts = array_slice($postStats, 0, 10);

        // Render the template with statistics
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
            'topPosts' => $topPosts,
        ]);
    }


}
