<?php
namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\Utilisateur;
use App\Entity\OffreEmploi;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OuvrierController extends AbstractController
{
    #[Route('/ouvrier/candidatures', name: 'ouvrier_candidatures')]
public function mesCandidatures(EntityManagerInterface $entityManager): Response
{
    // 🔥 Manually set Worker ID to 8 (Replace this with authentication later)
    $workerId = 8;

    // ✅ Fetch the worker (Ouvrier)
    $ouvrier = $entityManager->getRepository(Utilisateur::class)->find($workerId);

    if (!$ouvrier) {
        throw $this->createNotFoundException('Le travailleur avec l\'ID 8 n\'existe pas.');
    }

    // ✅ Get job applications submitted by this worker (with associated job offers)
    $candidatures = $entityManager->getRepository(Candidature::class)
        ->createQueryBuilder('c')
        ->leftJoin('c.idOffre', 'o') // Join OffreEmploi
        ->addSelect('o') // Select OffreEmploi data
        ->where('c.idTravailleur = :worker')
        ->setParameter('worker', $ouvrier)
        ->getQuery()
        ->getResult();

    // ✅ Get job offers created by this worker
    $offres = $entityManager->getRepository(OffreEmploi::class)->findBy(['id_employeur' => $ouvrier]);

    return $this->render('ouvrier/my_condidature.html.twig', [
        'candidatures' => $candidatures,
        'offres' => $offres,
        'ouvrier' => $ouvrier,
    ]);
}


#[Route('/offre/{id}', name: 'offre_show11', requirements: ['id' => '\d+'])]
public function show(OffreEmploi $offre): Response
{
    return $this->render('ouvrier/show.html.twig', [
        'offre' => $offre,
    ]);
}



}
