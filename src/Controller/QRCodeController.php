<?php

namespace App\Controller;

use App\Service\QrCodeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QrCodeController extends AbstractController
{
    #[Route('/qr-code', name: 'app_qr_code')]
    public function generateQrCode(QrCodeGenerator $qrCodeGenerator): Response
    {
        // Generate the QR code image as a base64 string
        $qrCodeImage = $qrCodeGenerator->generateDiscountQrCode();

        // Return the image in an HTML response
        return new Response($qrCodeImage, Response::HTTP_OK, ['Content-Type' => 'image/png']);
    }
}
