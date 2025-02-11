<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/produit')]
final class ProduitController extends AbstractController
{
    #[Route('/index',name: 'app_produit_index', methods: ['GET'])]
    public function index(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }
    #[Route('/index_front',name: 'app_produit_index1', methods: ['GET'])]
    public function index1(ProduitRepository $produitRepository): Response
    {
        return $this->render('produit_new/index.html.twig', [
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }
    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('', name: 'produits_front')]
    public function afficherCategories(): Response
    {
        $categories = [
            ['nom' => 'Matériel Agricole', 'image' => '/img/materiel_agricole.jpg'],
            ['nom' => 'Produits Agricoles', 'image' => '/img/produits_agricoles.jpg'],
            ['nom' => 'Produits Transformés', 'image' => '/img/produits_transformes.jpg'],
        ];
    
        return $this->render('produit_new/index.html.twig', [
            'categories' => $categories,
        ]);
    }
    
    
    #[Route('/produits/{categorie}', name: 'categorie_details')]
    public function afficherSousCategories(string $categorie): Response
    {
        $categories = [
            'Matériel Agricole' => [
                'image' => '/img/materiel_agricole.jpg',
                'sousCategories' => [
                    ['nom' => 'Tracteurs', 'image' => '/img/tracteurs.jpg'],
                    ['nom' => 'Moissonneuses-batteuses', 'image' => '/img/moisseuneuses-batteuses.jpg'],
                    ['nom' => 'Semoirs', 'image' => '/img/semoirs.jpg'],
                    ['nom' => 'Charrues', 'image' => '/img/charrues.jpg'],
                    ['nom' => 'Pulvérisateurs', 'image' => '/img/pulverisateurs.jpg'],
                ],
            ],
            'Produits Agricoles' => [
                'image' => '/img/produits_agricoles.jpg',
                'sousCategories' => [
                    ['nom' => 'Fruits', 'image' => '/img/fruits.jpg'],
                    ['nom' => 'Légumes', 'image' => '/img/legumes.jpg'],
                    ['nom' => 'Grains et céréales', 'image' => '/img/grains.jpg'],
                    ['nom' => 'Légumineuses', 'image' => '/img/legumineuses.jpg'],
                    ['nom' => 'Plantes oléagineuses', 'image' => '/img/plantes.jpg'],
                ],
            ],
            'Produits Transformés' => [
                'image' => '/img/produits_transformes.jpg',
                'sousCategories' => [
                    ['nom' => 'Confitures', 'image' => '/img/confitures.jpg'],
                    ['nom' => 'Jus de fruits', 'image' => '/img/jus.jpg'],
                    ['nom' => 'Huiles', 'image' => '/img/huiles.jpg'],
                    ['nom' => 'Farines', 'image' => '/img/farine.jpg'],
                    ['nom' => 'Produits secs', 'image' => '/img/fruitssecs.jpg'],
                ],
            ],
        ];
    
        if (!isset($categories[$categorie])) {
            throw $this->createNotFoundException('Catégorie introuvable.');
        }
    
        return $this->render('produit_new/souscategories.html.twig', [
            'categorie' => $categorie,
            'imageCategorie' => $categories[$categorie]['image'],
            'sousCategories' => $categories[$categorie]['sousCategories'],
        ]);
    }

    #[Route('/produits/sous-categorie/{sousCategorie}', name: 'produits_par_souscategorie')]
public function afficherProduitsParSousCategorie(string $sousCategorie, ProduitRepository $produitRepository): Response
{
    // Récupérer les produits correspondant à la sous-catégorie
    $produits = $produitRepository->findBy(['souscategorie' => $sousCategorie]);

    if (!$produits) {
        throw $this->createNotFoundException('Aucun produit trouvé pour cette sous-catégorie.');
    }

    return $this->render('produit_new/produits.html.twig', [
        'sousCategorie' => $sousCategorie,
        'produits' => $produits,
    ]);
}

    
}