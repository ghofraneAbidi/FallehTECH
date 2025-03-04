<?php

namespace App\Repository;

use App\Entity\Produit;
use App\Service\MailService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Event\LifecycleEventArgs;

class ProduitRepository extends ServiceEntityRepository
{
    private MailService $mailService;

    public function __construct(ManagerRegistry $registry, MailService $mailService)
    {
        parent::__construct($registry, Produit::class);
        $this->mailService = $mailService;
    }

    /**
     * This function is triggered when the stock of a product is updated.
     */
    public function checkStockAndSendAlert(Produit $produit): void
    {
        if ($produit->getStock() === 5) {
            $userEmail = 'ghofranhachana@gmail.com'; // Change this to the dynamic user email when ready
            $this->mailService->sendStockAlertEmail($userEmail, $produit->getName());
        }
    }

    public function countByCategorie(): array
{
    return $this->createQueryBuilder('p')
        ->select('c.nom as categorie, COUNT(p.id) as nombre')
        ->join('p.categorie', 'c')
        ->groupBy('c.nom')
        ->getQuery()
        ->getResult();
}
public function getStockEvolution(int $produitId): array
{
    return $this->createQueryBuilder('s')
        ->select("s.updatedAt AS date, s.stock AS stock")
        ->where('s.id = :produitId')
        ->setParameter('produitId', $produitId)
        ->orderBy('s.updatedAt', 'ASC')
        ->getQuery()
        ->getResult();
}
public function findLowStock(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.stock < :stockThreshold')
            ->setParameter('stockThreshold', 5)
            ->getQuery()
            ->getResult();
    }




    //    /**
    //     * @return Produit[] Returns an array of Produit objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Produit
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
