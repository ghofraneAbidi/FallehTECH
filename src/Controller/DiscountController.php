<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\DiscountCode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class DiscountController extends AbstractController
{
    /**
     * Apply discount code at checkout
     */
    #[Route('/apply-discount', name: 'apply_discount', methods: ['POST'])]
    public function applyDiscount(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $code = $request->request->get('discount_code');
        $discount = $entityManager->getRepository(DiscountCode::class)->findOneBy(['code' => $code]);

        if (!$discount || new \DateTime() > $discount->getExpiryDate()) {
            return $this->json(['error' => 'Invalid or expired discount code.'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'message' => 'Discount applied successfully!',
            'discount' => $discount->getDiscount(),
            'category' => $discount->getCategory()->getName()
        ]);
    }
}
