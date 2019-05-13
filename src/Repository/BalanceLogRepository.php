<?php

namespace App\Repository;

use App\Entity\BalanceLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BalanceLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method BalanceLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method BalanceLog[]    findAll()
 * @method BalanceLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BalanceLogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BalanceLog::class);
    }
}
