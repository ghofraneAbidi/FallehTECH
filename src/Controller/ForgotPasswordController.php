<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\TwilioService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ForgotPasswordController extends AbstractController
{
    private $entityManager;
    private $passwordHasher;
    private $twilioService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        TwilioService $twilioService
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->twilioService = $twilioService;
    }

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            // Find the user by email
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user) {
                // Generate a new temporary password
                $newPassword = bin2hex(random_bytes(8)); // Generates a random 16-character password
                $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);

                // Update the user's password
                $user->setPassword($hashedPassword);
                $this->entityManager->flush();

                // Send the new password via SMS
                $phoneNumber = $user->getPhoneNumber(); // Ensure your User entity has a phoneNumber field
                $message = "Your new password is: $newPassword. Please change it after logging in.";

                $this->twilioService->sendSms($phoneNumber, $message);

                $this->addFlash('success', 'A new password has been sent to your phone.');
                return $this->redirectToRoute('app_user_login');
            } else {
                $this->addFlash('error', 'No user found with this email address.');
            }
        }

        return $this->render('security/forgot_password.html.twig');
    }
}