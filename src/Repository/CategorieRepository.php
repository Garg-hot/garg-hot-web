<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Component\Pager\Pagination\PaginationInterface;


/**
 * @extends ServiceEntityRepository<Categorie>
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry,private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Categorie::class);
    }
    public function paginateCategories(int $page) :PaginationInterface //: Paginator
    {
        return $this->paginator->paginate(
            $this->createQueryBuilder('c'),//->leftJoin('c.plat', 'p')->select('c','p'), // pour avoir moins de requete
            $page,
            4,
            [
                'distinct' => false,
                'sortFieldAllowList' => ['c.id', 'c.nom']
            ]
        );
        // return new Paginator($this
        //     ->createQueryBuilder('c')
        //     -> setFirstResult(($page - 1) * $limit)
        //     -> setMaxResults($limit)
        //     ->getQuery()
        //     ->setHint(Paginator :: HINT_ENABLE_DISTINCT, false),
        //     false
        // );

    }
    //    /**
    //     * @return Categorie[] Returns an array of Categorie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Categorie
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
