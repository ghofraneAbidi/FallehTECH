<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
         $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
         // Récupérer les rôles de l'utilisateur
         $roles = $token->getRoleNames();

         // Si l'utilisateur a ROLE_ADMIN, rediriger vers le backoffice
         if (in_array('ROLE_ADMIN', $roles, true)) {
             return new RedirectResponse($this->router->generate('app_back_office'));
         }

         // Sinon, rediriger vers le frontoffice
         return new RedirectResponse($this->router->generate('app_front_office'));
    }
}
