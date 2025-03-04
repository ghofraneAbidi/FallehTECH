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
use App\Repository\SousCategorieRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Knp\Component\Pager\PaginatorInterface; 
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/produit')]
final class ProduitController extends AbstractController
{
    private $imagesDirectory;

    public function __construct(ParameterBagInterface $params)
    {
        $this->imagesDirectory = $params->get('produit_images_directory'); // ✅ Matches services.yaml
    }


   
    #[Route('/index', name: 'app_produit_index')]
    #[IsGranted('ROLE_ADMIN')]
public function index(ProduitRepository $produitRepository, PaginatorInterface $paginator, Request $request): Response
{
    $query = $produitRepository->findAll(); // Fetch all products
    
    // Paginate the results (8 per page)
    $produits = $paginator->paginate(
        $query, // Query to paginate
        $request->query->getInt('page', 1), // Current page (default: 1)
        8 // Number of results per page
    );

    return $this->render('produit/index.html.twig', [
        'produits' => $produits, // Pass paginated data
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
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
    
        if ($form->isSubmitted()) {
            dump($form->getData()); // Debug form data
            dump($form->get('imageFile')->getData()); // Debug image file data
    
            if ($form->isValid()) {
                $imageFile = $form->get('imageFile')->getData();
    
                if ($imageFile) {
                    dump("File received: " . $imageFile->getClientOriginalName()); // Check if file is received
                    
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = $slugger->slug($originalFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
    
                    // Move file
                    try {
                        $imageFile->move(
                            $this->getParameter('produit_images_directory'), // Use the correct parameter
                            $newFilename
                        );
                        
                        dump("File moved successfully!"); // Debug message
                    } catch (FileException $e) {
                        dump("Error moving file: " . $e->getMessage());
                        throw new \Exception('Error uploading file.');
                    }
    
                    $produit->setImage($newFilename);
                } else {
                    dump("No file uploaded!"); // Debug if no file is received
                }
    
                $entityManager->persist($produit);
                $entityManager->flush();
    
                return $this->redirectToRoute('app_produit_index');
            } else {
                dump("Form is not valid!"); // Debug validation errors
            }
        }
    
        return $this->render('produit/new.html.twig', [
            'form' => $form->createView(),
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
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($this->imagesDirectory, $newFilename);
                $produit->setImage($newFilename);
            }

            $entityManager->flush();
            return $this->redirectToRoute('app_produit_index');
        }

        return $this->render('produit/edit.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
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
            ['nom' => 'Matériel Agricole', 'image' => '/uploads/materiel_agricole.jpg'],
            ['nom' => 'Produits Agricoles', 'image' => '/uploads/produits_agricoles.jpg'],
            ['nom' => 'Produits Transformés', 'image' => '/uploads/produits_transformes.jpeg'],
        ];
    
    // ✅ Check if the user is logged in
    $user = $this->getUser();

    if ($user) {
        // ✅ Check if the user has the role "agriculteur"
        if ($user->getRole() === 'agriculteur') {
            return $this->render('base_agriculteur.html.twig', [
                'categories' => $categories,
            ]);
        }
    }

    // ✅ Default: Show normal front page for clients
    return $this->render('produit_new/index.html.twig', [
        'categories' => $categories,
    ]);
    }
    
    
    #[Route('/produits/{categorie}', name: 'categorie_details')]
    public function afficherSousCategories(string $categorie): Response
    {
        $categories = [
            'Matériel Agricole' => [
                'image' => '/uploads/materiel_agricole.jpg',
                'sousCategories' => [
                    ['nom' => 'Tracteurs', 'image' => '/uploads/tracteurs.jpg'],
                    ['nom' => 'Moissonneuses-batteuses', 'image' => '/uploads/moisseuneuses-batteuses.jpg'],
                    ['nom' => 'Semoirs', 'image' => '/uploads/semoirs.jpg'],
                    ['nom' => 'Charrues', 'image' => '/uploads/charrues.jpg'],
                    ['nom' => 'Pulvérisateurs', 'image' => '/uploads/pulverisateurs.jpg'],
                ],
            ],
            'Produits Agricoles' => [
                'image' => '/uploads/produits_agricoles.jpg',
                'sousCategories' => [
                    ['nom' => 'Fruits', 'image' => '/uploads/fruits.jpg'],
                    ['nom' => 'Légumes', 'image' => '/uploads/legumes.jpg'],
                    ['nom' => 'Grains et céréales', 'image' => '/uploads/grains.jpg'],
                    ['nom' => 'Légumineuses', 'image' => '/uploads/legumineuses.jpg'],
                    ['nom' => 'Plantes oléagineuses', 'image' => '/uploads/plantes.jpg'],
                ],
            ],
            'Produits Transformés' => [
                'image' => '/uploads/produits_transformes.jpg',
                'sousCategories' => [
                    ['nom' => 'Confitures', 'image' => '/uploads/confitures.jpg'],
                    ['nom' => 'Jus de fruits', 'image' => '/uploads/jus.jpg'],
                    ['nom' => 'Huiles', 'image' => '/uploads/huiles.jpg'],
                    ['nom' => 'Farines', 'image' => '/uploads/farine.jpg'],
                    ['nom' => 'Produits secs', 'image' => '/uploads/fruitssecs.jpg'],
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


#[Route('/get-souscategories/{id}', name: 'get_souscategories', methods: ['GET'])]
public function getSousCategories(int $id, SousCategorieRepository $sousCategorieRepository): JsonResponse
{
    // Récupérer les sous-catégories pour une catégorie donnée
    $sousCategories = $sousCategorieRepository->findBy(['categorie' => $id]);
    $data = [];

    foreach ($sousCategories as $sousCategorie) {
        $data[] = [
            'id' => $sousCategorie->getId(),
            'nom' => $sousCategorie->getNom(),
        ];
    }

    return new JsonResponse($data);
}
#[Route('/produits/sous-categorie/{sousCategorie}', name: 'produits_par_souscategorie')]
public function afficherProduitsParSousCategorie(string $sousCategorie, ProduitRepository $produitRepository, SousCategorieRepository $sousCategorieRepository): Response
{
    // Retrieve the SousCategorie object based on the name
    $sousCategorieObj = $sousCategorieRepository->findOneBy(['nom' => $sousCategorie]);

    if (!$sousCategorieObj) {
        throw $this->createNotFoundException('Sous-catégorie introuvable.');
    }

    // Retrieve products related to the SousCategorie
    $produits = $produitRepository->findBy(['sousCategorie' => $sousCategorieObj]);

    if (!$produits) {
        throw $this->createNotFoundException('Aucun produit trouvé pour cette sous-catégorie.');
    }

    return $this->render('produit_new/produits.html.twig', [
        'sousCategorie' => $sousCategorieObj,
        'produits' => $produits, // Ensure this is passed to Twig
    ]);
}
#[Route('/favoris/add/{id}', name: 'add_favoris', methods: ['POST'])]
public function addToFavorites(Produit $produit, EntityManagerInterface $em): JsonResponse
{
    try {
        // Crée une instance de la classe Favoris
        $favoris = new Favoris();
        $favoris->setProduit($produit);
        $favoris->setUserId(1);  // ID de l'utilisateur statique, ici 1
        $favoris->setIsFavorite(true);  // Optionnel, si tu veux indiquer qu'il est ajouté aux favoris

        // Sauvegarde dans la base de données
        $em->persist($favoris);
        $em->flush();

        return new JsonResponse(['success' => true, 'message' => 'Produit ajouté aux favoris']);
    } catch (\Exception $e) {
        return new JsonResponse(['error' => 'Une erreur est survenue. Veuillez réessayer.'], 500);
    }
}



   

}


