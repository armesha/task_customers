<?php

namespace App\Repository;

use App\Entity\Spending;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Spending>
 *
 * @method Spending|null find($id, $lockMode = null, $lockVersion = null)
 * @method Spending|null findOneBy(array $criteria, array $orderBy = null)
 * @method Spending[]    findAll()
 * @method Spending[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SpendingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Spending::class);
    }
}
