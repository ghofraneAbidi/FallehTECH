<?php
namespace App\Controller;

use App\Entity\Produit;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class MailController extends AbstractController
{
    #[Route('/mail/alert/{id}', name: 'app_mail_alert', methods: ['GET'])]
    public function sendStockAlert(Produit $produit, MailService $mailService, Request $request, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Email de l'agriculteur (à rendre dynamique)
        $userEmail = 'ziedalimi2244@gmail.com'; 

        if (!$produit) {
            $this->addFlash('error', 'Produit introuvable.');
            return $this->redirectToRoute('app_produit_index');
        }

        // Envoyer directement l'e-mail (sans Messenger)
        $mailService->sendStockAlertEmail($userEmail, $produit->getNom());

        $this->addFlash('success', "Alerte envoyée pour le produit {$produit->getNom()}.");

        return $this->redirectToRoute('app_produit_index');
    }
}