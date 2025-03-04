<?php

namespace App\Controller;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\OffreEmploi;
use App\Entity\Candidature;
use App\Entity\User;

use App\Entity\OuvrierCalendrier as EntityOuvrierCalendrier;
use App\Entity\OuvrierCalendrier;
use App\Form\CandidatureType;
use App\Form\CandidatureNewType;
use App\Form\OuvrierCalendrierType;

use App\Repository\CandidatureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


#[Route('/candidature')]
final class CandidatureController extends AbstractController
{
    #[Route('/index', name: 'app_candidature_index', methods: ['GET'])]

    public function index(Request $request, PaginatorInterface $paginator, EntityManagerInterface $entityManager): Response
    {
        $query = $entityManager->getRepository(Candidature::class)->createQueryBuilder('c')->getQuery();

        $candidatures = $paginator->paginate(
            $query, /* Query */
            $request->query->getInt('page', 1), /* Current page number */
            10 /* Items per page */
        );

        return $this->render('candidature/index.html.twig', [
            'candidatures' => $candidatures,
        ]);
    }
    
    #[Route('/{id}', name: 'app_candidature_list', methods: ['GET'])]
    public function list(OffreEmploi $offre, CandidatureRepository $candidatureRepository): Response
    {
        // Get all applications for the selected job offer
        $candidatures = $candidatureRepository->findBy(['idOffre' => $offre]);

        return $this->render('candidature/index.html.twig', [
            'offre' => $offre,
            'candidatures' => $candidatures,
        ]);
    }
    #[Route('/candidature/new', name: 'app_candidature_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $candidature = new Candidature();
    $form = $this->createForm(CandidatureNewType::class, $candidature);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // ✅ Retrieve the selected worker from the form
        $selectedWorker = $form->get('idTravailleur')->getData();

        // ✅ Ensure a worker was selected
        if (!$selectedWorker) {
            $this->addFlash('error', 'Veuillez sélectionner un travailleur.');
            return $this->redirectToRoute('app_candidature_new');
        }

        // ✅ Assign the selected worker to the candidature
        $candidature->setIdTravailleur($selectedWorker);

        $entityManager->persist($candidature);
        $entityManager->flush();

        $this->addFlash('success', 'Candidature créée avec succès.');

        return $this->redirectToRoute('app_candidature_index');
    }

    return $this->render('candidature/new.html.twig', [
        'form' => $form->createView(),
    ]);
}

    
    #[Route('show/{id}', name: 'app_candidature_show', methods: ['GET'])]
    public function show(CandidatureRepository $candidatureRepository, int $id): Response
    {
        $candidature = $candidatureRepository->find($id);
    
        if (!$candidature) {
            throw $this->createNotFoundException('Candidature not found.');
        }
    
        return $this->render('candidature/show.html.twig', [
            'candidature' => $candidature,
        ]);
    }
    

    #[Route('/{id}/edit', name: 'app_candidature_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CandidatureType::class, $candidature);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
        }
    
        // Retrieve the username from the linked Utilisateur entity
        $username = $candidature->getIdTravailleur()->getName();
    
        return $this->render('candidature/edit.html.twig', [
            'candidature' => $candidature,
            'form' => $form->createView(),
            'username' => $username, // Pass the username to the template
        ]);
    }
    

    #[Route('/{id}', name: 'app_candidature_delete', methods: ['POST'])]
    public function delete(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$candidature->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($candidature);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_candidature_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/front/{id}', name: 'app_candidature_delete_front', methods: ['POST'])]
    public function deletefront(Request $request, Candidature $candidature, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$candidature->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($candidature);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ouvrier_offers', [], Response::HTTP_SEE_OTHER);
    }



    #[Route('/ouvrier/offers', name: 'app_ouvrier_offers')]
    public function ouvrierOffers(EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // Fetch all job offers
        $offres = $entityManager->getRepository(OffreEmploi::class)->findAll();
    
        // Get the impersonated user (User entity)
        $userId = $session->get('impersonated_user_id');
        $user = null;
        $userCandidatures = [];
        $calendarData = [];
    
        if ($userId) {
            $user = $entityManager->getRepository(User::class)->find($userId);
    
            // Fetch candidatures (applications) for the user
            $userCandidatures = $entityManager->getRepository(Candidature::class)
                ->findBy(['idTravailleur' => $userId]);
    
            // Fetch bookings for the user
            $calendarData = $entityManager->getRepository(OuvrierCalendrier::class)
                ->findBy(['ouvrier' => $user]); // Assuming the relation is with "ouvrier"
        }
    
        return $this->render('worker/ouvrier_offers.html.twig', [
            'offres' => $offres,
            'candidatures' => $userCandidatures,
            'calendarData' => $calendarData, // Ensure this is passed
            'user' => $user,
        ]);
    }



}
