<?php
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Psr\Log\LoggerInterface;

class MailService
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function sendStockAlertEmail(string $toEmail, string $productName)
    {
        $this->logger->info("Tentative d'envoi d'un e-mail de stock bas à : " . $toEmail);

        $email = (new Email())
            ->from('sarafaleh76@gmail.com')
            ->to($toEmail)
            ->subject('Alerte Stock Faible')
            ->text("Le stock du produit $productName est bas.")
            ->html("
                <p>Bonjour,</p>
                <p>Le stock du produit <strong>$productName</strong> est bas.</p>
                <p>Pensez à le réapprovisionner rapidement.</p>
                <p>Cordialement,</p>
                <p>L'équipe de gestion</p>
            ");

        try {
            // ✅ Corrected: Ensure $email is passed correctly
            $this->mailer->send($email);
            $this->logger->info("E-mail de stock bas envoyé avec succès à $toEmail.");
        } catch (\Exception $e) {
            $this->logger->error("Échec de l'envoi de l'e-mail : " . $e->getMessage());
        }
    }
}
