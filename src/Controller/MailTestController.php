<?php
namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class MailTestController extends AbstractController
{
    #[Route('/test/email', name: 'test_email')]
    public function sendTestEmail(MailerInterface $mailer, LoggerInterface $logger): Response
    {
        // 📌 Replace with the agricultor's real email
        $agricultorEmail = 'sarah.faleh@esprit.tn';

        // 📌 Fake product name for testing
        $productName = 'Test Product';

        // 📨 Create the email
        $email = (new Email())
            ->from(new Address('sarafaleh76@gmail.com')) // Your sender email
            ->to($agricultorEmail)
            ->subject("⚠️ Stock Alert: $productName")
            ->html("
                <h2>Stock Alert for $productName</h2>
                <p>The stock for <strong>$productName</strong> is low.</p>
                <p>Please restock as soon as possible.</p>
                <p>Best regards,</p>
                <p>Your Store Management Team</p>
            ");

        // 🚀 Try sending the email
        try {
            $logger->info("🚀 Attempting to send email to: " . $agricultorEmail);
            $mailer->send($email); // Send it
            
            $logger->info("✅ Email sent successfully to: " . $agricultorEmail);
            return new Response("✅ Test email sent successfully to $agricultorEmail!");
        
        } catch (TransportExceptionInterface $e) {
            // ❌ Log the error
            $logger->error("❌ Error sending email: " . $e->getMessage());

            // Debugging output
            $debugInfo = [
                'SMTP Host' => $_ENV['MAILER_DSN'] ?? 'Not Set',
                'Sender Email' => 'sarafaleh76@gmail.com',
                'Recipient Email' => $agricultorEmail,
                'Error Message' => $e->getMessage(),
            ];
            
            $logger->error("📌 Debugging Info: " . json_encode($debugInfo));

            return new Response("❌ Error sending email: " . $e->getMessage() . "<br><pre>" . print_r($debugInfo, true) . "</pre>");
        }
    }
}
