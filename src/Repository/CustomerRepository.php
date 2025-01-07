<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customer>
 */
// Repository pro praci s databazi zakazniku
class CustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    // Vyhledavani zakazniku podle filtru
    // Podporuje: hledani podle jmena, filtrovani podle vydaju, razeni
    public function findByFilters(?string $name = null, ?float $minTotalSpent = null, ?float $maxTotalSpent = null, string $sortBy = 'name', string $sortOrder = 'ASC'): array
    {
        // Zakladni dotaz s joinovanymi vydaji
        $qb = $this->createQueryBuilder('c')
            ->select('c.id, c.name')
            ->addSelect('COALESCE(SUM(s.amount), 0) as total_spent')
            ->leftJoin('c.spendings', 's')
            ->groupBy('c.id, c.name');

        if ($name) {
            $qb->andWhere('c.name LIKE :name')
               ->setParameter('name', '%' . $name . '%');
        }

        if ($minTotalSpent !== null) {
            $qb->having('total_spent >= :minTotalSpent')
               ->setParameter('minTotalSpent', $minTotalSpent);
        }

        if ($maxTotalSpent !== null) {
            $qb->having('total_spent <= :maxTotalSpent')
               ->setParameter('maxTotalSpent', $maxTotalSpent);
        }

        // Validate and apply sorting
        $sortOrder = strtoupper($sortOrder);
        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'ASC';
        }

        if ($sortBy === 'total_spent') {
            $qb->orderBy('total_spent', $sortOrder);
        } else {
            $qb->orderBy('c.name', $sortOrder);
        }

        $results = $qb->getQuery()->getArrayResult();

        return array_map(function ($row) {
            return [
                'id' => $row['id'],
                'name' => $row['name'],
                'total_spent' => (float)$row['total_spent']
            ];
        }, $results);
    }
}
