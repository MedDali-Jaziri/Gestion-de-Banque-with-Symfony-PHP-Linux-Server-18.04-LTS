<?php

namespace App\Repository;

use App\Entity\Compte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Compte|null find($id, $lockMode = null, $lockVersion = null)
 * @method Compte|null findOneBy(array $criteria, array $orderBy = null)
 * @method Compte[]    findAll()
 * @method Compte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Compte::class);
    }

    public function getDecouvert($solde)
    {

        return $this->getEntityManager()
                    ->createQuery(
                        "SELECT c.codeCompte FROM App:Compte c WHERE c.solde = '$solde'" 
                    )
                ->getResult();


        /*$em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb =  $em->createQueryBuilder();

        $nots = $em->createQueryBuilder("
        SELECT decouvert FROM App:Compte WHERE code_compte='$codeCompte'       
        ")->getSingleScalarResult();

        try{
            return $nots;
        }catch(\Doctrine\ORM\NoResultException $e) {

            return null;
            
        }*/
    }

    // /**
    //  * @return Compte[] Returns an array of Compte objects
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
    public function findOneBySomeField($value): ?Compte
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
