<?php

namespace App\Controller;

use App\Entity\OffreEmploi;
use App\Entity\User;
use App\Entity\Candidature;
use App\Entity\OuvrierCalendrier;
use Psr\Log\LoggerInterface;
use App\Enum\StatutCandidature;
use App\Form\CandidatureType;
use App\Form\OuvrierCalendrierType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Form\OffreEmploiType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use App\Service\CalendarService; // Import the correct service
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/offre/emploi')] 
final class OffreEmploiController extends BaseController

{
    #[Route(name: 'app_offre_emploi_index', methods: ['GET'])]
public function index(EntityManagerInterface $entityManager, Request $request): Response
{
    $showMyOffers = $request->query->get('my_offers'); // Check if "Only My Offers" is selected

    $offres = $entityManager->getRepository(OffreEmploi::class)->findAll(); // Default: show all offers

    // Get the logged-in user
    $loggedInUser = $this->getUser();

    // If "Only My Offers" is selected and user is logged in
    if ($showMyOffers && $loggedInUser) {
        $offres = $entityManager->getRepository(OffreEmploi::class)->findBy(['id_employeur' => $loggedInUser]); // Show only user's offers
    }

    return $this->render('index_front.html.twig', [
        'offres' => $offres,
        'user' => $loggedInUser,  // ✅ Fix: Pass a single user, not a collection
        'showMyOffers' => $showMyOffers, 
    ]);
}


    
    
    
    



   


    #[Route('/worker/offre/new', name: 'app_worker_offre_new', methods: ['GET', 'POST'])]
    public function new1(Request $request, EntityManagerInterface $entityManager): JsonResponse|Response
    {
        $offre = new OffreEmploi();
        $form = $this->createForm(OffreEmploiType::class, $offre);
        $form->handleRequest($request);
    
        if ($request->isXmlHttpRequest()) { // ✅ Handle AJAX request
            if ($form->isSubmitted() && $form->isValid()) {
                // Retrieve the logged-in user
                $employer = $this->getUser();
    
                if (!$employer) {
                    return new JsonResponse([
                        'status' => 'error',
                        'globalError' => "No logged-in user found. Please log in first."
                    ]);
                }
    
                $offre->setIdEmployeur($employer);
                $entityManager->persist($offre);
                $entityManager->flush();
    
                return new JsonResponse(['status' => 'success']);
            }
    
            // ✅ Handle validation errors
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $origin = $error->getOrigin();
                if ($origin) {
                    $errors[$origin->getName()] = $error->getMessage();
                }
            }
    
            return new JsonResponse(['status' => 'error', 'errors' => $errors]);
        }
    
        return $this->render('offre_front/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    
    

    #[Route('/show/{id}', name: 'app_worker_offre_show1', methods: ['GET', 'POST'])]
    public function show1(OffreEmploi $offre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidature = new Candidature();
        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $candidature->setIdTravailleur($this->getUser());
            $candidature->setIdOffre($offre);

            $entityManager->persist($candidature);
            $entityManager->flush();

            $this->addFlash('success', 'Your application has been submitted successfully!');
            return $this->redirectToRoute('app_worker_offre_list');
        }

        return $this->render('offre_front/show_offre.html.twig', [
            'offre' => $offre,
            'form' => $form->createView(),
        ]);
    }

   
    #[Route('/{id}/editfront', name: 'app_offre_emploi_edit_front', methods: ['GET', 'POST'])]
    public function editfront(Request $request, OffreEmploi $offreEmploi, EntityManagerInterface $entityManager): JsonResponse|Response
    {
        $form = $this->createForm(OffreEmploiType::class, $offreEmploi);
        $form->handleRequest($request);
    
        if ($request->isXmlHttpRequest()) { // ✅ Handle AJAX request
            if ($form->isSubmitted() && $form->isValid()) {
                try {
                    $entityManager->flush();
                    
                    return new JsonResponse([
                        'status' => 'success',
                        'message' => 'Job Offer Updated Successfully!'
                    ]);
                } catch (\Exception $e) {
                    return new JsonResponse([
                        'status' => 'error',
                        'globalError' => 'An unexpected error occurred: ' . $e->getMessage()
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
    
            // ✅ Collect validation errors
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $origin = $error->getOrigin();
                if ($origin) {
                    $errors[$origin->getName()] = $error->getMessage();
                }
            }
    
            return new JsonResponse([
                'status' => 'error',
                'errors' => $errors
            ], Response::HTTP_BAD_REQUEST);
        }
    
        // ✅ If it's a normal request (not AJAX), return the HTML form
        return $this->render('offre_front/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    

    #[Route('/offre/emploi/front/{id}', name: 'app_offre_emploi_delete_front', methods: ['POST'])]
    public function deletefront(Request $request, OffreEmploi $offreEmploi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $offreEmploi->getId(), $request->request->get('_token'))) {
            $entityManager->remove($offreEmploi);
            $entityManager->flush();
            $this->addFlash('success', 'Offre supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_offre_emploi_index', [], Response::HTTP_SEE_OTHER);
    }

    

  
    #[Route('/ouvrier/apply/{id}', name: 'app_worker_apply', methods: ['POST'])]
    public function applyToOffer(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        try {
            // ✅ Fetch the job offer
            $offre = $entityManager->getRepository(OffreEmploi::class)->find($id);
            if (!$offre) {
                throw new \Exception("Job offer not found.");
            }
    
            // ✅ Get logged-in worker
            $loggedInUser = $this->getUser();
            if (!$loggedInUser) {
                throw new \Exception("User not logged in.");
            }
    
            // ✅ Check if the worker already has an application for this job
            $existingCandidature = $entityManager->getRepository(Candidature::class)
                ->findOneBy(['idOffre' => $offre, 'idTravailleur' => $loggedInUser]);
    
            if ($existingCandidature) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'You have already applied for this job.'
                ], Response::HTTP_BAD_REQUEST);
            }
    
            // ✅ Get the job's start and end date
            $offerStartDate = $offre->getStartDate();
            $offerEndDate = $offre->getDateExpiration();
    
            if (!$offerStartDate || !$offerEndDate) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid job dates. Please check with the employer.'
                ], Response::HTTP_BAD_REQUEST);
            }
    
            // ✅ Create a new job application
            $candidature = new Candidature();
            $candidature->setIdOffre($offre);
            $candidature->setIdTravailleur($loggedInUser);
            $candidature->setStatut(StatutCandidature::EN_ATTENTE);
    
            $entityManager->persist($candidature);
    
            // ✅ Create calendar entry (pending)
            $calendarEntry = new OuvrierCalendrier();
            $calendarEntry->setOuvrier($loggedInUser);
            $calendarEntry->setCandidature($candidature);
            $calendarEntry->setStartDate($offerStartDate);
            $calendarEntry->setEndDate($offerEndDate);
            $calendarEntry->setStatus('en_attente');
    
            $entityManager->persist($calendarEntry);
            $entityManager->flush();
    
            return $this->json([
                'status' => 'success',
                'message' => 'Your job application has been submitted and is awaiting approval.'
            ]);
    
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    //****************************BACK***************************** */

    //DESPLAY
#[Route('/all-offers', name: 'app_all_offers', methods: ['GET'])]
    public function allOffers(EntityManagerInterface $entityManager): Response
    {
        $offres = $entityManager->getRepository(OffreEmploi::class)->findAll(); // Fetch all offers
    
        return $this->render('offre_emploi/index.html.twig', [
            'offre_emplois' => $offres, // Pass all job offers to Twig
        ]);
    }




    //Delete
    #[Route('/offre/emploi/{id}', name: 'app_offre_emploi_delete', methods: ['POST'])]
    public function delete(Request $request, OffreEmploi $offreEmploi, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $offreEmploi->getId(), $request->request->get('_token'))) {
            $entityManager->remove($offreEmploi);
            $entityManager->flush();
            $this->addFlash('success', 'Offre supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_all_offers', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_offre_emploi_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OffreEmploi $offreEmploi, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OffreEmploiType::class, $offreEmploi);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            if (!$form->isValid()) {
                $this->addFlash('danger', 'Veuillez corriger les erreurs dans le formulaire.');
            } else {
                // Ensure the logged-in user is assigned as the employer
                $employer = $this->getUser();
    
                if (!$employer) {
                    throw $this->createNotFoundException("No logged-in user found. Please log in first.");
                }
    
                $offreEmploi->setIdEmployeur($employer); // Assign employer before updating
                
                $entityManager->flush();
                $this->addFlash('success', 'Offre mise à jour avec succès!');
    
                return $this->redirectToRoute('app_offre_emploi_show', ['id' => $offreEmploi->getId()]);
            }
        }
    
        return $this->render('offre_emploi/edit.html.twig', [
            'offre_emploi' => $offreEmploi,
            'form' => $form->createView(),
        ]);
    }


    

    //aDD NEW
    #[Route('/offre/new', name: 'app_offre_emploi_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offreEmploi = new OffreEmploi();
        $form = $this->createForm(OffreEmploiType::class, $offreEmploi);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Retrieve the logged-in user
            $employer = $this->getUser();
    
            if (!$employer) {
                throw $this->createNotFoundException("No logged-in user found. Please log in first.");
            }
    
            $offreEmploi->setIdEmployeur($employer);
            $entityManager->persist($offreEmploi);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_all_offers');
        }
    
        return $this->render('offre_emploi/new.html.twig', [
            'offreEmploi' => $offreEmploi,
            'form' => $form->createView(),
        ]);
    }



    //SHOW

    #[Route('/show/back/{id}', name: 'app_worker_offre_show_back', methods: ['GET', 'POST'])]
    public function show(OffreEmploi $offre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidature = new Candidature();
        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $candidature->setIdTravailleur($this->getUser());
            $candidature->setIdOffre($offre);

            $entityManager->persist($candidature);
            $entityManager->flush();

            $this->addFlash('success', 'Your application has been submitted successfully!');
            return $this->redirectToRoute('app_worker_offre_list');
        }

        return $this->render('offre_emploi/show.html.twig', [
            'offre_emploi' => $offre,
            'form' => $form->createView(),
        ]);
    }


//select condida for offre

#[Route('/farmer/applications/{id}', name: 'app_farmer_applications')]
public function viewApplications(int $id, EntityManagerInterface $entityManager): Response
{
    $farmer = $this->getUser();
    if (!$farmer) {
        throw $this->createAccessDeniedException("You must be logged in as a farmer.");
    }

    // Get the specific job offer
    $jobOffer = $entityManager->getRepository(OffreEmploi::class)->findOneBy([
        'id' => $id,
        'id_employeur' => $farmer // Ensures the farmer owns the job offer
    ]);

    if (!$jobOffer) {
        throw $this->createNotFoundException("Job offer not found or you don't have permission to view it.");
    }

    // Get applications related to the specific job offer
    $applications = $entityManager->getRepository(Candidature::class)->findBy(['idOffre' => $jobOffer]);

    return $this->render('offre_front/offre_condidature.html.twig', [
        'applications' => $applications,
        'farmer' => $farmer,
        'jobOffer' => $jobOffer
    ]);
}


#[Route('/farmer/application/accept/{id}', name: 'app_farmer_accept_application', methods: ['POST'])]
public function acceptApplication(int $id, EntityManagerInterface $entityManager): JsonResponse
{
    $candidature = $entityManager->getRepository(Candidature::class)->find($id);
    if (!$candidature) {
        return new JsonResponse(['status' => 'error', 'message' => 'Application not found.'], Response::HTTP_NOT_FOUND);
    }

    // ✅ Update application status to "Accepted"
    $candidature->setStatut(StatutCandidature::ACCEPTEE);
    $entityManager->flush();

    // ✅ Update the existing calendar entry
    $calendarEntry = $entityManager->getRepository(OuvrierCalendrier::class)
        ->findOneBy(['ouvrier' => $candidature->getIdTravailleur(), 'startDate' => $candidature->getIdOffre()->getStartDate()]);

    if ($calendarEntry) {
        $calendarEntry->setStatus('accepted'); // Update status to accepted
        $entityManager->flush();
    }

    return new JsonResponse([
        'status' => 'success',
        'message' => 'Application accepted. Worker is now scheduled.'
    ]);
}

#[Route('/farmer/application/reject/{id}', name: 'app_farmer_reject_application', methods: ['POST'])]
public function rejectApplication(int $id, EntityManagerInterface $entityManager): JsonResponse
{
    // ✅ Fetch the application
    $candidature = $entityManager->getRepository(Candidature::class)->find($id);
    
    if (!$candidature) {
        return new JsonResponse(['status' => 'error', 'message' => 'Application not found.'], Response::HTTP_NOT_FOUND);
    }

    // ✅ Update application status to "Rejected"
    $candidature->setStatut(StatutCandidature::REFUSEE);

    // ✅ Find and update the corresponding calendar entry
    $calendarEntry = $entityManager->getRepository(OuvrierCalendrier::class)
        ->findOneBy(['ouvrier' => $candidature->getIdTravailleur(), 'startDate' => $candidature->getIdOffre()->getStartDate()]);

    if ($calendarEntry) {
        $calendarEntry->setStatus('rejetee'); // Change status to "Rejected"
        $entityManager->persist($calendarEntry);
    }

    // ✅ Save changes to database
    $entityManager->flush();

    return new JsonResponse([
        'status' => 'success',
        'message' => 'Application rejected successfully.',
        'candidature_id' => $id
    ]);
}

#[Route('/candidature/{id}/complete', name: 'app_candidature_complete', methods: ['POST'])]
public function completeCandidature(int $id, EntityManagerInterface $em): Response
{
    $candidature = $em->getRepository(Candidature::class)->find($id);

    if (!$candidature) {
        throw $this->createNotFoundException('Candidature not found.');
    }

    if ($candidature->getStatut() !== StatutCandidature::ACCEPTEE) {
        return new Response('Only accepted candidatures can be marked as completed.', 400);
    }

    $candidature->setStatut(StatutCandidature::TERMINEE);
    $em->flush();

    return $this->redirectToRoute('app_ouvrier_offers'); // Redirect after completion
}



#[Route('/candidature/{id}/confirm', name: 'app_confirm_completion', methods: ['POST'])]
public function confirmCompletion(
    int $id,
    Request $request,
    EntityManagerInterface $entityManager,
    CsrfTokenManagerInterface $csrfTokenManager
): Response {
    // Validate CSRF Token
    $csrfToken = $request->request->get('_token');
    if (!$csrfTokenManager->isTokenValid(new \Symfony\Component\Security\Csrf\CsrfToken('confirm' . $id, $csrfToken))) {
        return $this->redirectToRoute('app_farmer_applications', [
            'error' => 'Invalid CSRF token.'
        ]);
    }

    // Find the candidature
    $candidature = $entityManager->getRepository(Candidature::class)->find($id);
    if (!$candidature) {
        throw $this->createNotFoundException('Candidature not found.');
    }

    // Ensure it's in the "Completed" state before confirmation
    if ($candidature->getStatut() !== StatutCandidature::TERMINEE) {
        return $this->redirectToRoute('app_farmer_dashboard', [
            'error' => 'Only completed applications can be confirmed.'
        ]);
    }

    // Get the submitted rating and feedback
    $rating = $request->request->get('rating');
    $comment = $request->request->get('comment');

    // Validate Rating
    if (!in_array($rating, [1, 2, 3, 4, 5])) {
        return $this->redirectToRoute('app_farmer_dashboard', [
            'error' => 'Invalid rating value.'
        ]);
    }

    // Update the candidature
    $candidature->setStatut(StatutCandidature::CONFIRMEE);
    $candidature->setRating((int)$rating);
    //$candidature->setFeedback($comment);

    $entityManager->flush();

    return $this->redirectToRoute('app_farmer_applications', [
        'id' => $candidature->getIdOffre()->getId(),
        'success' => 'Completion confirmed and review submitted successfully!'
    ]);
    
}
#[Route('/ouvrier/accepted-offers', name: 'app_worker_accepted_offers')]
public function acceptedOffers(EntityManagerInterface $entityManager): Response
{
    // Get the logged-in user
    $loggedInUser = $this->getUser();
    if (!$loggedInUser) {
        return $this->redirectToRoute('app_login'); // Redirect if no user is found
    }

    // Fetch accepted job applications for the logged-in user
    $acceptedOffers = $entityManager->getRepository(Candidature::class)->createQueryBuilder('c')
        ->join('c.idOffre', 'o')
        ->where('c.idTravailleur = :worker')
        ->andWhere('c.statut = :statut') // Only accepted offers
        //->setParameter('worker', $loggedInUser->getId())
        ->setParameter('statut', 'acceptee')
        ->getQuery()
        ->getResult();

    // Convert offers to JSON format for FullCalendar.js
    $events = [];

    foreach ($acceptedOffers as $candidature) {
        $dateDebut = $candidature->getIdOffre()->getDateExpiration();

        if (!$dateDebut) {
            continue; // Skip if dateDebut is null
        }

        $dateExpiration = (clone $dateDebut)->modify('+10 days'); // Auto-set end date

        $events[] = [
            'title' => $candidature->getIdOffre()->getTitre(),
            'start' => $dateDebut->format('Y-m-d\TH:i:s'),
            'end' => $dateExpiration->format('Y-m-d\TH:i:s'),
        ];
    }

    return $this->render('ouvrier/accepted_offers.html.twig', [
        'offers' => $events,
        'loggedInUser' => $loggedInUser,
    ]);
}





  


}
