<?php

namespace App\Repository;

use App\Entity\MasterCategorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function GuzzleHttp\Psr7\str;

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

    public function getMostAdvancedCampagne($catId){
        $campagnesIds = [];
        $words = $this->getPreCategorieNames($catId);
        $conn = $this->getEntityManager()
            ->getConnection();
        $sql = "SELECT c.id
            FROM campagne c 
            left Join participation p on p.campagne_id = c.id 
            where ".implode(" OR ", $words) ."
            GROUP BY c.id
            ORDER BY SUM(p.quantity) DESC
            LIMIT 8 ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll();

        if(!empty($result)){
            foreach ($result as $id){
                array_push($campagnesIds,$id['id']);
            }
        }
        return ($campagnesIds);
    }

    public function getPreCategorieNames($masterCatId){
        $conn = $this->getEntityManager()
            ->getConnection();
        $sql = "SELECT c.name FROM `categorie` c join master_categorie_categorie mc on c.id = mc.categorie_id Where mc.master_categorie_id = :masterCatId ";
        $stmt = $conn->prepare($sql);
        $stmt->execute(["masterCatId" => $masterCatId]);
        $array = $stmt->fetchAll();
        return array_map(function ($val){
            return "description LIKE '%".$val['name']."%' OR titre LIKE '%".$val['name']."%'";
        },$array);
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
