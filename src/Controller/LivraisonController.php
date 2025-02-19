<?php

namespace App\Controller;
use App\Repository\CommandeRepository;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use App\Entity\Livraison;
use App\Form\LivraisonType;
use App\Repository\LivraisonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/livraison')]
final class LivraisonController extends AbstractController
{
    #[Route(name: 'app_livraison_index', methods: ['GET'])]
    public function index(LivraisonRepository $livraisonRepository): Response
    {
        return $this->render('livraison/index.html.twig', [
            'livraisons' => $livraisonRepository->findAll(),
        ]);
    }


        #[Route('/new', name: 'app_livraison_new', methods: ['GET', 'POST'])]
        public function new(Request $request, EntityManagerInterface $entityManager, CommandeRepository $commandeRepository, LivraisonRepository $livraisonRepository): Response
        {
            $livraison = new Livraison();
            $commandeId = $request->query->get('commandeId');
            $commande = $commandeRepository->find($commandeId);
            $existingLivraison = $livraisonRepository->findOneBy(['commande' => $commande]);
    
            if (!$commande) {
                // Handle the case where the commande doesn't exist
                $this->addFlash('error', 'Commande not found');
                return $this->redirectToRoute('app_commande_index');
            }
    
            if ($existingLivraison) {
                // Handle the case where the livraison already exists
                $this->addFlash('warning', 'Commande déjà livrée.');
                return $this->redirectToRoute('app_livraison_index');
            }
    
            if ($commandeId) {
                $commande = $commandeRepository->find($commandeId);
                if ($commande) {
                    $livraison->setCommande($commande);
                    // Update the commande status to "en cours"
                    $commande->setStatus('Confirmée');
                    $entityManager->persist($commande);
                }
            }
    
            $form = $this->createForm(LivraisonType::class, $livraison);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($livraison);
                $entityManager->flush();
    
                // Flash success message
                $this->addFlash('success', 'Livraison créée avec succès.');
                return $this->redirectToRoute('app_livraison_index', [], Response::HTTP_SEE_OTHER);
            }
    
            return $this->render('livraison/new.html.twig', [
                'livraison' => $livraison,
                'form' => $form,
            ]);
        }
    


    #[Route('/{id}', name: 'app_livraison_show', methods: ['GET'])]
    public function show(Livraison $livraison): Response
    {
        return $this->render('livraison/show.html.twig', [
            'livraison' => $livraison,
            'commande' => $livraison->getCommande(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_livraison_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Livraison $livraison, EntityManagerInterface $entityManager): Response
    {
        


        $form = $this->createForm(LivraisonType::class, $livraison, [
            'is_edit' => true, // Correctly passing the is_edit option
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_livraison_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('livraison/edit.html.twig', [
            'livraison' => $livraison,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_livraison_delete', methods: ['POST'])]
    public function delete(Request $request, Livraison $livraison, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$livraison->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($livraison);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_livraison_index', [], Response::HTTP_SEE_OTHER);
    }
}
