<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Form\ProduitType;
use App\Entity\Produit;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CategorieRepository;
use App\Repository\SousCategorieRepository;

#[Route('/agriculteur')]
class AgriculteurController extends AbstractController
{
    #[Route('/', name: 'agriculteur_index', methods: ['GET', 'POST'])]
    public function index(
        ProduitRepository $produitRepository,
        CategorieRepository $categorieRepository,
        SousCategorieRepository $sousCategorieRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // **1️⃣ Création d'un nouvel objet Produit**
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        // **2️⃣ Gestion du formulaire**
        if ($form->isSubmitted() && $form->isValid()) {
            // **Gestion du téléchargement d'image**
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('produit_images_directory'), $newFilename);

                $produit->setImage($newFilename);
            }

            // **Enregistrement du produit en base de données**
            $entityManager->persist($produit);
            $entityManager->flush();

            // ✅ Message de confirmation
            $this->addFlash('success', '✅ Produit ajouté avec succès !');

            return $this->redirectToRoute('agriculteur_index');
        }

        // **3️⃣ Récupération des catégories et des produits**
        $sousCategories = $sousCategorieRepository->findAll(); // ✅ Récupère toutes les sous-catégories
        $categories = $categorieRepository->findAll(); // ✅ Récupère toutes les catégories
        $produits = $produitRepository->findAll(); // ✅ Récupère tous les produits sans pagination

        return $this->render('agriculteur/index.html.twig', [
            'produits' => $produits,
            'form' => $form->createView(),
            'categories' => $categories, // ✅ Envoi des catégories à la vue
            'sousCategories' => $sousCategories, // ✅ Envoi des sous-catégories à la vue
            'allProduits' => $produits, // ✅ Variable contenant tous les produits
        ]);
    }



    /**
     * 📌 Ajouter un produit
     */
    #[Route('/produit/new', name: 'agriculteur_produit_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $produit = new Produit();
            $produit->setNom($request->request->get('nom'));
            $produit->setPrix($request->request->get('prix'));
            $produit->setDescription($request->request->get('description'));
            $produit->setStock($request->request->get('stock'));

            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('agriculteur_index');
        }

        return $this->render('agriculteur/new.html.twig');
    }

    /**
     * 📌 Modifier un produit
     */
    

     #[Route('/produit/edit/{id}', name: 'agriculteur_produit_edit', methods: ['GET', 'POST'])]
     public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
     {
         $form = $this->createForm(ProduitType::class, $produit);
         $form->handleRequest($request);
     
         if ($form->isSubmitted() && $form->isValid()) {
             $imageFile = $form->get('imageFile')->getData();
             if ($imageFile) {
                 $newFilename = uniqid().'.'.$imageFile->guessExtension();
                 $imageFile->move($this->getParameter('produit_images_directory'), $newFilename);
                 $produit->setImage($newFilename);
             }
     
             $entityManager->flush();
             $this->addFlash('success', '✅ Produit modifié avec succès !');
     
             return $this->redirectToRoute('agriculteur_index');
         }
     
         return $this->render('agriculteur/edit.html.twig', [
             'form' => $form->createView(),
             'produit' => $produit,
         ]);
     }
     
     

    

    




    /**
     * 📌 Supprimer un produit
     */
    #[Route('/produit/delete/{id}', name: 'agriculteur_produit_delete', methods: ['POST'])]
    public function delete(Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($produit);
        $entityManager->flush();

        return $this->redirectToRoute('agriculteur_index');
    }
    #[Route('/favoris/list', name: 'client_favorites_list')]
public function listFavoris(FavorisRepository $favorisRepository): Response
{
    $favorites = $favorisRepository->findAll();

    return $this->render('favoris/list.html.twig', [
        'favorites' => $favorites,
    ]);
}

}
