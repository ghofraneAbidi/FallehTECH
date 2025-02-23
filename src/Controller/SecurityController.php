<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Check if the user is already logged in
        if ($this->getUser()) {
            $roles = $this->getUser()->getRoles(); // Get user roles

            // If the user is an admin, redirect to the back office
            if (in_array('ROLE_ADMIN', $roles)) {
                return $this->redirectToRoute('app_back_office'); 
            }

            // If the user is an agricultor, redirect to their dashboard
            if (in_array('ROLE_AGRICULTEUR', $roles)) {
                return $this->redirectToRoute('agriculteur_index'); 
            }

            // If the user is a client, redirect to the front office (produits)
            return $this->redirectToRoute('produits_front'); 
        }

        // Get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
