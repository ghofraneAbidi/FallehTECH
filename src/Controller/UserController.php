<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;


use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;



#[Route('/user')]
final class UserController extends AbstractController
{

    #[Route('/home', name: 'app_home_front')]
    public function homePage(): Response
    {
        return $this->render('frontoffice/index.html.twig', [
            'title' => 'Falleh Tech - Home',
        ]);
    }
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->redirectToRoute('app_user_login');
    }

    #[Route('/logout', name: 'app_user_logout')]
    public function logout(): void
    {
        // Cette méthode ne sera jamais appelée, Symfony intercepte la route.
        throw new \Exception('Cette méthode ne sera jamais exécutée');
    }

    #[Route('/profile', name: 'app_user_profile')]
    public function profile(): Response
    {
        return $this->render('backoffice/profile.html.twig');
    }

    #[Route('/profilefront', name: 'app_user_profilefront')]
    public function profilefront(): Response
    {
        return $this->render('frontoffice/profile.html.twig');
    }

    #[Route('/signup', name: 'app_user_signup', methods: ['GET', 'POST'])]
    public function signup(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);

            // Add +216 prefix to the phone number
            $phoneNumber = $user->getPhoneNumber();
            $user->setPhoneNumber('+216' . $phoneNumber);

            // Save the user
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_login', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('signup/index.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

      #[Route('/login', name: 'app_user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastEmail = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_email' => $lastEmail,
            'error' => $error,
        ]);
    } 

    #[Route('/deleteFront', name: 'app_user_delete_front', methods: ['POST'])]
    public function deleteFront(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur connecté
        /** @var \App\Entity\User|null $user */
        $user = $this->getUser();
    
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour supprimer votre compte.');
        }
    
        // Vérifier le token CSRF
        if ($this->isCsrfTokenValid('delete_account' , $request->request->get('token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
    
            // Déconnecter l'utilisateur : vider le token et invalider la session
            $this->container->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();
        }
    
        return $this->redirectToRoute('app_user_signup');
    }

    #[Route('/{id}/toggle-active', name: 'admin_user_toggle_active', methods: ['POST'])]
    public function toggleActive(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_active' . $user->getId(), $request->request->get('_token'))) {
            // Inverse l'état actif
            $user->setActive(!$user->isActive());
            $entityManager->flush();
            
            $this->addFlash('success', 'L\'utilisateur a été ' . ($user->isActive() ? 'activé' : 'bloqué') . '.');
        }
        
        return $this->redirectToRoute('app_user_show', ['id' => $user->getId()]);
    }


    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {           
            $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $user->getPassword()
        );
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_back_office', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    
    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('backoffice/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            $entityManager->flush();

            return $this->redirectToRoute('app_back_office', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('backoffice/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /*#[Route('/{id}/editFront', name: 'app_user_edit_front', methods: ['GET', 'POST'])]
    public function editFront(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);

        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            dump('Form is submitted and valid!');

            $newPassword = $form->get('password')->getData();
            dump($newPassword);
            if ($newPassword && $newPassword !== "") {
                $user->setPassword($newPassword);
                dump($user);
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('app_front_office', [], Response::HTTP_SEE_OTHER);
        }
        dump('Form not submitted or invalid!');

        return $this->render('frontoffice/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }*/

    #[Route('/{id}/editFront', name: 'app_user_edit_front', methods: ['GET', 'POST'])]
    public function editFront(Request $request, User $user, EntityManagerInterface $entityManager,UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $newPassword = $form->get('password')->getData();
            if ($newPassword && $newPassword !== "") {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $newPassword                
                );
                $user->setPassword($hashedPassword);
            } else {
                // Keep the old password
                $user->setPassword($user->getPassword());
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('app_front_office', [], Response::HTTP_SEE_OTHER);
        }
        dump('Form not submitted or invalid!');

        return $this->render('frontoffice/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_back_office', [], Response::HTTP_SEE_OTHER);
    }  










    

}
