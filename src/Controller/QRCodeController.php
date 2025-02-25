<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Color\Color;
use Symfony\Component\Filesystem\Filesystem;

class QRCodeController extends AbstractController
{
    #[Route('/generate-qr', name: 'generate_qr')]
    public function generateQrCode(): Response
    {
        // ✅ Generate absolute URL for the game
        $gameUrl = "http://192.168.56.1:8000/game"; 

        // ✅ Create a new QR Code (Using correct syntax for v6)
        $qrCode = new QrCode($gameUrl);
        $qrCode->getEncoding(new Encoding('UTF-8'));
        $qrCode->getErrorCorrectionLevel(ErrorCorrectionLevel::High);
        $qrCode->getSize(300);
        $qrCode->getMargin(10);
        $qrCode->getForegroundColor(new Color(0, 0, 0));
        $qrCode->getBackgroundColor(new Color(255, 255, 255));

        // ✅ Write QR Code as PNG
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // ✅ Save the QR Code in /public/qr_codes directory
        $qrCodeDir = $this->getParameter('kernel.project_dir') . '/public/qr_codes';
        if (!is_dir($qrCodeDir)) {
            mkdir($qrCodeDir, 0777, true);
        }
        $qrCodePath = $qrCodeDir . '/game_qr.png';
        file_put_contents($qrCodePath, $result->getString());

        return new Response('<h2>QR Code Generated Successfully!</h2><br> <img src="/qr_codes/game_qr.png" />');
    }

    #[Route('/game', name: 'game')]
    public function game(): Response
    {
        return $this->render('game/index.html.twig', [
            'message' => 'Welcome to the Agriculture Game!',
        ]);
    }
}
