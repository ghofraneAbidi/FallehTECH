<?php
namespace App\Repository;

use App\Entity\Favoris;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FavorisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favoris::class);
    }

    // Custom queries can be added here if needed, for example:
    public function findFavoritesByUser(int $userId)
    {
        return $this->createQueryBuilder('f')
            ->where('f.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }
}
