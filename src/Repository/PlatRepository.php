<?php

namespace App\Repository;

use App\Entity\Plat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Plat>
 */
class PlatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plat::class);
    }
    /**
     * @return Plat[]
     */
    public function findDurationLowerThan(int $duration): array{
        return $this->createQueryBuilder('plat')
            -> where ('plat.duration <= :duration')
            -> orderBy ('plat.duration', 'ASC')
            //->andWhere('recipe.category=1')// ici on affiche les recettes selon l'id category
            ->setMaxResults(10)
            ->setParameter('duration', $duration)
            -> getQuery()
            ->getResult();
    }
    public function findTotalDuration(){
        return $this->createQueryBuilder('plat')
            ->select('SUM(plat.duration) AS total')
            ->getQuery()
            ->getResult();
    }

}
