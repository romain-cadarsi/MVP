<?php

namespace App\Repository;

use App\Entity\MasterCategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MasterCategorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method MasterCategorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method MasterCategorie[]    findAll()
 * @method MasterCategorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MasterCategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MasterCategorie::class);
    }

    // /**
    //  * @return MasterCategorie[] Returns an array of MasterCategorie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MasterCategorie
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
