<?php

namespace App\Controller;

use App\Repository\OffreEmploiRepository;
use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(OffreEmploiRepository $offreRepo, EntityManagerInterface $entityManager): Response
    {
        // ✅ Fetch job offers from database
        $offres = $offreRepo->findAll();

        // ✅ Fetch users from database using EntityManager
        $users = $entityManager->getRepository(Utilisateur::class)->findAll();

        return $this->render('index_front.html.twig', [
            'offres' => $offres,
            'users' => $users, // ✅ Pass users to the template
        ]);
    }


    #[Route('/login', name: 'app_login')]
public function login( OffreEmploiRepository $offreRepo): Response
{
    $offres = $offreRepo->findAll();

    return $this->render('worker/index.html.twig',['offres' => $offres,]);
}
#[Route('/utilisateur/{id}', name: 'app_utilisateur_show', methods: ['GET'])]
public function show(Utilisateur $utilisateur): Response
{
    return $this->render('utilisateur/show.html.twig', [
        'utilisateur' => $utilisateur,
    ]);
}


#[Route('/map', name: 'app_utilisateur_map', methods: ['GET'])]
public function map( ): Response
{
 

    return $this->render('map/map.html.twig');
}


}
