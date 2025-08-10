<?php

namespace App\Repository;

use App\Entity\DynamicTaskMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DynamicTaskMessage>
 */
class DynamicTaskMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DynamicTaskMessage::class);
    }

    /**
     * @return DynamicTaskMessage[]
     */
    public function findActiveTasksForScheduling(): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.active = :active')
            ->setParameter('active', true)
            ->orderBy('t.priority', 'DESC')
            ->addOrderBy('t.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
