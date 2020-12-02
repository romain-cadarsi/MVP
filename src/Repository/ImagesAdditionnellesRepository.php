<?php

namespace App\Repository;

use App\Entity\ImagesAdditionnelles;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ImagesAdditionnelles|null find($id, $lockMode = null, $lockVersion = null)
 * @method ImagesAdditionnelles|null findOneBy(array $criteria, array $orderBy = null)
 * @method ImagesAdditionnelles[]    findAll()
 * @method ImagesAdditionnelles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImagesAdditionnellesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImagesAdditionnelles::class);
    }

    // /**
    //  * @return ImagesAdditionnelles[] Returns an array of ImagesAdditionnelles objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ImagesAdditionnelles
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
