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
use Knp\Component\Pager\PaginatorInterface;

#[Route('/agriculteur')]
class AgriculteurController extends AbstractController
{
    #[Route('/', name: 'agriculteur_index', methods: ['GET', 'POST'])]
    public function index(
        ProduitRepository $produitRepository,
        PaginatorInterface $paginator,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // **1️⃣ Créer un nouvel objet Produit**
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        // **2️⃣ Vérifier si le formulaire est soumis et valide**
        if ($form->isSubmitted() && $form->isValid()) {
            // **Gérer l'image uploadée**
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->getParameter('produit_images_directory'), $newFilename);

                $produit->setImage($newFilename);
            }

            // **Sauvegarder le produit en base de données**
            $entityManager->persist($produit);
            $entityManager->flush();
// ✅ Ajouter le message flash avant la redirection
$this->addFlash('success', '✅ Produit ajouté avec succès !');

return $this->redirectToRoute('agriculteur_index');
        }

        // **3️⃣ Récupérer les produits paginés**
        $query = $produitRepository->createQueryBuilder('p')->getQuery();
        $produits = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            9
        );

        return $this->render('agriculteur/index.html.twig', [
            'produits' => $produits,
            'form' => $form->createView(),
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
