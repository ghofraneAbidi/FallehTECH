<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    #[Route('/game', name: 'game_page')]
    public function gamePage(): Response
    {
        return $this->render('game.html.twig'); // Game page
    }

    #[Route('/game/win', name: 'game_win')]
    public function gameWin(SessionInterface $session): Response
    {
        // ✅ Mark the discount as won
        $session->set('discount_applied', true);

        // ✅ Redirect back to apply the discount
        return $this->redirectToRoute('apply_discount');
    }
}
