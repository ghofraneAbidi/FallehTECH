<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commande')]
final class CommandeController extends AbstractController
{

    #[Route(name: 'app_commande_index', methods: ['GET'])]
    public function index(CommandeRepository $commandeRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_commande_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $users = [
        ['id' => 1, 'name' => 'Alice Johnson', 'address' => '123 Main St, City A'],
        ['id' => 2, 'name' => 'Bob Smith', 'address' => '456 Oak St, City B'],
        ['id' => 3, 'name' => 'Charlie Davis', 'address' => '789 Pine St, City C'],
        ['id' => 4, 'name' => 'Diana White', 'address' => '101 Maple Ave, City D'],
        ['id' => 5, 'name' => 'Ethan Brown', 'address' => '202 Elm St, City E'],
    ];

    // Simulated panier data (ID => Total Price)
    $panier = [
        ['id' => 1, 'total' => 250.00],
        ['id' => 2, 'total' => 140.00],
        ['id' => 3, 'total' => 99.50]
    ];
    $selectedTotal = null;
    $selectedPanierId = $request->get('commande')['panierId'] ?? null;
    if ($selectedPanierId) {
        foreach ($panier as $p) {
            if ($p['id'] == $selectedPanierId) {
                $selectedTotal = $p['total'];
                break;
            }
        }
    }

    $commande = new Commande();
    $commande->setDateCreation(new \DateTime()); // Default date
    $commande->setTotal(0); // Default total
    $commande->setStatus('En Attente'); // Default status
    $commande->setModePaiement('Espece'); // Default payment method
    $commande->setStatusPaiement('En Attente'); // Default payment status

    // Pass panier to the form (add it as 'panier' option)
    $form = $this->createForm(CommandeType::class, $commande, [
        'users' => $users,
        'panier' => $panier,
       
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        

        $entityManager->persist($commande);
        $entityManager->flush();

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('commande/new.html.twig', [
        'form' => $form->createView(),
        'users' => $users, // Pass users to Twig
        'panier' => $panier, // Pass panier data to Twig (again)
    ]);
}
    

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
{
    // Determine if we're editing
    $form = $this->createForm(CommandeType::class, $commande, options: [
        'is_edit' => true, // Pass 'true' since this is an edit form
        'users' => [
            ['id' => 1, 'name' => 'Alice Johnson', 'address' => '123 Main St, City A'],
            ['id' => 2, 'name' => 'Bob Smith', 'address' => '456 Oak St, City B'],
            ['id' => 3, 'name' => 'Charlie Davis', 'address' => '789 Pine St, City C'],
            ['id' => 4, 'name' => 'Diana White', 'address' => '101 Maple Ave, City D'],
            ['id' => 5, 'name' => 'Ethan Brown', 'address' => '202 Elm St, City E'],
        ], // Pass the list of users
    ]);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('commande/edit.html.twig', [
        'commande' => $commande,
        'form' => $form->createView(),
    ]);
}

    #[Route('/{id}', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }
}
