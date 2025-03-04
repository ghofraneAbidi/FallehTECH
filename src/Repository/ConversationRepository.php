<?php
// src/Repository/ConversationRepository.php
namespace App\Repository;

use App\Entity\Conversation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ConversationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conversation::class);
    }

    // Custom query example (Find conversations for a user)
    public function findByUser($user)
    {
        return $this->createQueryBuilder('c')
            ->where('c.user1 = :user OR c.user2 = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
