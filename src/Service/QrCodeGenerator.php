<?php

namespace App\Service;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\Result\PngResult;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request; // ✅ Correct import

class QrCodeGenerator
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function generateDiscountQrCode(): string
    {
        // Use the correct local network IP instead of 127.0.0.1
        $serverIp = '192.168.31.149'; // Ensure this matches your computer's IP
        $gameUrl = "http://$serverIp:8000/game";
        
        // ✅ Create the QR code object (NO `create()` method)
        $qrCode = new QrCode($gameUrl);

        // ✅ Create the writer
        $writer = new PngWriter();

        // ✅ Generate the PNG result
        $result = $writer->write($qrCode);

        // ✅ Return the QR code as a base64 string
        return $result->getDataUri();
    }
}



