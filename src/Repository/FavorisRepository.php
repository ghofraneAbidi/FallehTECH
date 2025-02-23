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

    // Method to find all favorites for a given user
    public function findFavorisByUser(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.user = :user') // ✅ Correct field name
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
