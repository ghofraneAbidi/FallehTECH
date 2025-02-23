<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

class QRCodeController extends AbstractController
{
    #[Route('/game', name: 'game')]
    public function index(): Response
    {
        return $this->render('game/index.html.twig', [
            'message' => 'Welcome to the Game!',
        ]);
    }
    #[Route('/game/qrcode', name: 'game_qrcode')]
    public function generateGameQRCode(UrlGeneratorInterface $urlGenerator): Response
    {
        try {
            // Generate absolute URL for the game page
            $gameUrl = $urlGenerator->generate('game', [], UrlGeneratorInterface::ABSOLUTE_URL);

            // Create the QR Code correctly for Endroid v4+
            $qrCode = QrCode::create($gameUrl) // Use create() instead of new QrCode()
                ->withEncoding(new Encoding('UTF-8'))
                ->withErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
                ->withSize(300)
                ->withMargin(10);

            // Use PNG writer to generate the QR image
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            // Return the QR Code as a response
            return new Response($result->getString(), 200, ['Content-Type' => $result->getMimeType()]);
        } catch (\Exception $e) {
            return new Response('Error generating QR Code: ' . $e->getMessage(), 500);
        }
    }
}
