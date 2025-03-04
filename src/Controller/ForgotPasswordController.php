<?php 
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
    
use Egulias\EmailValidator\Validation\RFCValidation;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ForgotPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, MailerInterface $mailer       ): Response
    {
        if ($request->isMethod('GET')) {
            return $this->render('forgot_password/index.html.twig');
        }
    
        // Handle the POST request (sending the reset code)
        $emailuser = $request->request->get('email');
    
        if (!$emailuser) {
            return new Response('No email provided', Response::HTTP_BAD_REQUEST);
        }
    
        // Generate a random 6-digit code
        $resetCode =11; //random_int(100000, 999999);
    
     
        $email = (new TemplatedEmail())
        ->from('chaher.dridi.6@gmail.com')
        ->to($emailuser)  // L'adresse de l'utilisateur qui a fait la demande
        ->subject('Your Password Reset Code')
        ->html("<p>Your password reset code is: <strong>$resetCode</strong></p>");

            
        $mailer->send($email);

        return new Response('Password reset code sent successfully.');
    }
    
}
