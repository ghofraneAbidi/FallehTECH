<?php

namespace App\Controller;

use App\Service\QrCodeGenerator;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/cart')]
class CartController extends AbstractController
{
    //  View Cart (Renders the cart page)
    #[Route('/', name: 'cart_view', methods: ['GET'])]
public function viewCart(SessionInterface $session, QrCodeGenerator $qrCodeGenerator): Response
{
    $cart = $session->get('cart', []);
    $discountApplied = $session->get('discount_applied', false);
    $totalPrice = 0;

    foreach ($cart as &$item) {
        if ($discountApplied && !isset($item['discount_applied'])) {
            $item['price'] = round($item['price'] * 0.85, 2); // Apply discount
            $item['discount_applied'] = true;
        }
        $totalPrice += $item['price'] * $item['quantity'];
    }

    // ✅ Ensure the cart updates correctly in session
    if ($discountApplied) {
        $session->set('cart', $cart);
    }

    // Generate QR code
    $qrCode = $qrCodeGenerator->generateDiscountQrCode();

    return $this->render('cart.html.twig', [
        'cart' => $cart,
        'cartTotal' => number_format($totalPrice, 2), // Format to 2 decimal places
        'discountApplied' => $discountApplied,
        'qrCode' => $qrCode,
    ]);
}


    // ➕ Add Product to Cart
    #[Route('/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function addToCart($id, Request $request, SessionInterface $session, ProduitRepository $produitRepository): JsonResponse
    {
        $cart = $session->get('cart', []);
        $data = json_decode($request->getContent(), true);
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;

        $produit = $produitRepository->find($id);
        if (!$produit) {
            return new JsonResponse(['success' => false, 'message' => 'Produit introuvable'], 404);
        }

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] += $quantity;
        } else {
            $cart[$id] = [
                'id' => $id,
                'name' => $produit->getNom(),
                'image' => $produit->getImage(),
                'price' => $produit->getPrix(),
                'quantity' => $quantity,
                'stock' => $produit->getStock() 
            ];
        }

        $session->set('cart', $cart);
        return new JsonResponse(['success' => true, 'cart' => $cart]);
    }

   
    

    // ❌ Remove Product from Cart
    #[Route('/remove/{id}', name: 'cart_remove', methods: ['POST'])]
    public function removeFromCart($id, SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);

        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
        }

        return new JsonResponse(['success' => true, 'cart' => $cart]);
    }

    // 🗑 Clear Cart
    #[Route('/cart/checkout', name: 'cart_checkout', methods: ['POST'])]
public function checkout(SessionInterface $session, EntityManagerInterface $entityManager, ProduitRepository $produitRepository): JsonResponse
{
    $cart = $session->get('cart', []);

    if (empty($cart)) {
        return new JsonResponse(['success' => false, 'message' => 'Le panier est vide.']);
    }

    foreach ($cart as $id => $item) {
        $produit = $produitRepository->find($id);
        if (!$produit) continue;

        // Vérifier si le stock est suffisant
        if ($produit->getStock() < $item['quantity']) {
            return new JsonResponse(['success' => false, 'message' => "Stock insuffisant pour " . $produit->getNom()]);
        }

        // Mise à jour du stock
        $produit->setStock($produit->getStock() - $item['quantity']);
        $entityManager->persist($produit);
    }

    $entityManager->flush();
    $session->set('cart', []); // ✅ Vide le panier après la commande
    $session->remove('discount_applied'); // ✅ Réinitialise la réduction

    return new JsonResponse(['success' => true]);
}


    private function sendStockAlert($produit, MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('sarafaleh76@gmail.com')
            ->to('sarah.faleh@esprit.tn')
            ->subject('⚠️ Stock Faible : ' . $produit->getNom())
            ->html("<p>Attention ! Le produit <strong>{$produit->getNom()}</strong> n'a plus que 3 unités en stock.</p>");

        $mailer->send($email);
    }

    #[Route('/cart/count', name: 'cart_count', methods: ['GET'])]
    public function countCart(SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);
        return new JsonResponse(['count' => count($cart)]);
    }

    #[Route('/clear', name: 'cart_clear', methods: ['POST'])]
public function clearCart(SessionInterface $session): JsonResponse
{
    $session->set('cart', []);
    $session->remove('discount_applied'); // ✅ Réin
    return new JsonResponse(['success' => true]);
}
#[Route('/play-game', name: 'play_game', methods: ['GET'])]
public function playGame(): Response
{
    return $this->render('game.html.twig'); // This is the game page
}
#[Route('/apply-discount', name: 'apply_discount', methods: ['GET'])]
public function applyDiscount(SessionInterface $session): Response
{
    // ✅ Check if the discount has already been applied
    if ($session->get('discount_applied')) {
        return $this->redirectToRoute('cart_view'); // Redirect to cart
    }

    // ✅ Retrieve cart from session
    $cart = $session->get('cart', []);
    if (empty($cart)) {
        return $this->redirectToRoute('cart_view'); // If cart is empty, go back to cart
    }

    // ✅ Apply 15% discount to each product
    foreach ($cart as &$item) {
        if (!isset($item['discount_applied'])) {
            $item['price'] = round($item['price'] * 0.85, 2);
            $item['discount_applied'] = true;
        }
    }

    // ✅ Update session
    $session->set('cart', $cart);
    $session->set('discount_applied', true);

    // ✅ Redirect to cart
    return $this->redirectToRoute('cart_view');
}


}
