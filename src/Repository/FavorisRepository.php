<?php

namespace App\Repository;

use App\Entity\Favoris;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FavorisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Favoris::class);
    }

    // ✅ Find all favorites for a given user
    public function findFavoritesByUser(int $userId): array
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.userId = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();
    }

    // ✅ Find suggested products based on the categories of the user's favorite products
    public function findSuggestedProducts(int $userId, int $limit = 10): array
    {
        $entityManager = $this->getEntityManager();
    
        $query = $entityManager->createQuery(
            'SELECT p FROM App\Entity\Produit p
            WHERE p.categorie IN (
                SELECT DISTINCT c.id FROM App\Entity\Favoris fav
                JOIN fav.produit pFav
                JOIN pFav.categorie c
                WHERE fav.userId = :userId
            )
            AND p.id NOT IN (
                SELECT DISTINCT pExcl.id FROM App\Entity\Favoris favExcl
                JOIN favExcl.produit pExcl
                WHERE favExcl.userId = :userId
            )'
        )
        ->setParameter('userId', $userId)
        ->getResult();
    
        // ✅ Shuffle results in PHP since Doctrine DQL does not support ORDER BY RAND()
        shuffle($query);
    
        // ✅ Return only the requested number of results
        return array_slice($query, 0, $limit);
    }
    
}
