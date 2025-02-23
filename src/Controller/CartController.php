<?php

namespace App\Controller;

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
    // 🛒 View Cart (Renders the cart page)
    #[Route('/', name: 'cart_view', methods: ['GET'])]
    public function viewCart(SessionInterface $session): Response
    {
        return $this->render('cart.html.twig', [
            'cart' => $session->get('cart', [])
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
                'quantity' => $quantity
            ];
        }

        $session->set('cart', $cart);
        return new JsonResponse(['success' => true, 'cart' => $cart]);
    }

    // 🔄 Update Cart Quantity
    #[Route('/update/{id}', name: 'cart_update', methods: ['POST'])]
    public function updateCart($id, Request $request, SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);
        $data = json_decode($request->getContent(), true);
        $quantity = isset($data['quantity']) ? intval($data['quantity']) : 1;

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = max(1, $cart[$id]['quantity'] + $quantity);
            $session->set('cart', $cart);
        }

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
    #[Route('/clear', name: 'cart_clear', methods: ['POST'])]
    public function clearCart(SessionInterface $session): JsonResponse
    {
        $session->set('cart', []);
        return new JsonResponse(['success' => true]);
    }
    #[Route('/cart/count', name: 'cart_count', methods: ['GET'])]
public function countCart(SessionInterface $session): JsonResponse
{
    $cart = $session->get('cart', []);
    return new JsonResponse(['count' => count($cart)]);
}

}
