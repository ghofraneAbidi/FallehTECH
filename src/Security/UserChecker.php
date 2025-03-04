<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        // On vérifie que l'utilisateur est bien de type App\Entity\User
        if (!$user instanceof User) {
            return;
        }

        // Si l'utilisateur n'est pas actif (bloqué), on empêche l'authentification
        if (!$user->isActive()) {
            throw new CustomUserMessageAccountStatusException('Votre compte est bloqué.');
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
        // Vous pouvez ajouter ici d'autres vérifications post-authentification si nécessaire.
    }
}
