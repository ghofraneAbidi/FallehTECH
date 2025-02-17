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
        $output->writeln('-------started login checking----------');
        // Vérifier si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            $output->writeln('Admin user created successfully!');
            dump($this->getUser()->getRoles()); // Pour voir les rôles
            dump(in_array('ROLE_ADMIN', $this->getUser()->getRoles())); // Pour vérifier si ROLE_ADMIN est présent

            // Si c'est un admin, redirection vers le backoffice
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
                return $this->redirectToRoute('app_back_office'); // votre route de backoffice
            }
            // Sinon redirection normale pour les autres utilisateurs
            return $this->redirectToRoute('app_front_office'); // ou autre route par défaut
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    //#[Route('/logout', name: 'app_logout')]
    //public function logout(): void
    //{
        // Cette méthode peut rester vide - elle sera interceptée par la configuration de sécurité
      //  throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    //}
}