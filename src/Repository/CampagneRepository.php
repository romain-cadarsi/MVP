<?php

namespace App\Repository;

use App\Entity\Campagne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Campagne|null find($id, $lockMode = null, $lockVersion = null)
 * @method Campagne|null findOneBy(array $criteria, array $orderBy = null)
 * @method Campagne[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CampagneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Campagne::class);
    }

    public function findAll()
    {
        return $this->findBy(array(), array('id' => 'DESC'));
    }

    public function searchForKeyWords($words){
        $campagnesIds = [];
        $conn = $this->getEntityManager()
            ->getConnection();
        $sql = "SELECT DISTINCT id FROM `campagne` where titre like :words OR description like :words order by debut_campagne desc ";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array('words' => "%".$words."%"));
        $result = $stmt->fetchAll();
        if(!empty($result)){
            foreach ($result as $id){
                array_push($campagnesIds,$id['id']);
            }
        }
        return $this->findBy(['id' => $campagnesIds],["debutCampagne" => "desc"]);
    }

    public function getMostAdvancedCampagne(){
        $campagnesIds = [];
        $conn = $this->getEntityManager()
            ->getConnection();
        $sql = "SELECT c.id
FROM campagne c
Join participation p on p.campagne_id = c.id
GROUP BY c.id
ORDER BY SUM(p.quantity) DESC
LIMIT 8";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();
        if(!empty($result)){
            foreach ($result as $id){
                array_push($campagnesIds,$id['id']);
            }
        }
        return $this->findBy(['id' => $campagnesIds]);
    }

    // /**
    //  * @return Campagne[] Returns an array of Campagne objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Campagne
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
