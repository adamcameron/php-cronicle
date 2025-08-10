<?php

namespace App\Repository;

use App\Entity\BankHoliday;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BankHoliday>
 */
class BankHolidayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankHoliday::class);
    }

    public function isHoliday(DateTimeInterface $date): bool
    {
        $holiday = $this->findOneBy(['date' => $date]);
        return $holiday !== null;
    }

    public function findHolidaysInYear(int $year): array
    {
        return $this->createQueryBuilder('bh')
            ->where('YEAR(bh.date) = :year')
            ->setParameter('year', $year)
            ->orderBy('bh.date', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function clearAndReplaceHolidays(array $holidayData): void
    {
        $this->createQueryBuilder('bh')
            ->delete()
            ->getQuery()
            ->execute();

        $em = $this->getEntityManager();
        foreach ($holidayData as $data) {
            $holiday = new BankHoliday();
            $holiday->setDate(new DateTimeImmutable($data['date']));
            $holiday->setTitle($data['title']);
            
            $em->persist($holiday);
        }
        
        $em->flush();
    }
}
