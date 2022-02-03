<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\Race;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Race|null find($id, $lockMode = null, $lockVersion = null)
 * @method Race|null findOneBy(array $criteria, array $orderBy = null)
 * @method Race[]    findAll()
 * @method Race[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Race::class);
    }

    /**
     * @return Race[] Returns an array of Race objects
     */
    public function findRecentFinished(?int $maxResults = null)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.finishedAt IS NOT NULL')
            ->orderBy('r.finishedAt', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Race[] Returns an array of Race objects
     */
    public function findJoinableRaces(User $user, Category $category)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.finishedAt IS NULL')
            ->andWhere('r.category = :category')
            ->leftJoin('r.results', 'rr', 'WITH', 'rr.user = :user')
            ->setParameters(['category' => $category, 'user' => $user])
            ->andWhere('rr.user IS NULL')
            ->getQuery()
            ->getResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Race
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
