<?php

namespace App\Repository;

use App\Entity\RaceResult;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RaceResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method RaceResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method RaceResult[]    findAll()
 * @method RaceResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RaceResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RaceResult::class);
    }

    /**
     * @return RaceResult[] Returns an array of RaceResult objects
     */
    public function findByUserNotFinished(User $user)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.finishedAt IS NULL')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?RaceResult
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
