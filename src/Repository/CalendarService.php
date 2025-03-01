<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\OuvrierCalendrier;
use App\Entity\Utilisateur;

class CalendarService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function isWorkerBusy(Utilisateur $worker, \DateTime $startDate, \DateTime $endDate): bool
    {
        try {
            if (!$startDate || !$endDate) {
                error_log("ERROR: Invalid start or end date provided for worker {$worker->getId()}.");
                return false;
            }
    
            $qb = $this->entityManager->createQueryBuilder();
            $qb->select('COUNT(oc.id)')
                ->from(OuvrierCalendrier::class, 'oc')
                ->where('oc.ouvrier = :worker')
                ->andWhere(
                    $qb->expr()->orX(
                        ':startDate BETWEEN oc.startDate AND oc.endDate',
                        ':endDate BETWEEN oc.startDate AND oc.endDate',
                        'oc.startDate BETWEEN :startDate AND :endDate',
                        'oc.endDate BETWEEN :startDate AND :endDate'
                    )
                )
                ->setParameter('worker', $worker)
                ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
                ->setParameter('endDate', $endDate->format('Y-m-d H:i:s'));
    
            $count = $qb->getQuery()->getSingleScalarResult();
            error_log("DEBUG: Worker {$worker->getId()} check ({$startDate->format('Y-m-d H:i:s')} to {$endDate->format('Y-m-d H:i:s')}) - Conflicts Found: $count");
    
            return $count > 0; // ✅ Return true if worker is already booked
        } catch (\Exception $e) {
            error_log("ERROR in isWorkerBusy(): " . $e->getMessage());
            return false; // ✅ Avoid application crash
        }
    }
    

    
}
