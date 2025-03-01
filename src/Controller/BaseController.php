<?php
// src/Controller/BaseController.php
namespace App\Controller;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends AbstractController
{
    protected EntityManagerInterface $entityManager;
    protected SessionInterface $session;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->session = $requestStack->getSession();
    }

    public function getImpersonatedUser(): ?Utilisateur
    {
        $impersonatedUserId = $this->session->get('impersonated_user_id');

        if ($impersonatedUserId) {
            return $this->entityManager->getRepository(Utilisateur::class)->find($impersonatedUserId);
        }

        return null;
    }

    public function getGlobalTwigData(): array
    {
        return [
            'impersonated_user' => $this->getImpersonatedUser(),
        ];
    }
}
