<?php

namespace App\Repository;

use App\Entity\Personne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;
/**
 * @extends ServiceEntityRepository<Personne>
 *
 * @method Personne|null find($id, $lockMode = null, $lockVersion = null)
 * @method Personne|null findOneBy(array $criteria, array $orderBy = null)
 * @method Personne[]    findAll()
 * @method Personne[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personne::class);
    }

    public function save(Personne $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Personne $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
    * @return Personne[] Returns an array of Personne objects
    */
    public function findPersonnesByAgeInterval($ageMin,$ageMax): array
    {   $qb=$this->createQueryBuilder('p');
        $qb=$this->createQueryBuilder('p');
            
            return $qb->getQuery()
            ->getResult()
        ;
    }

    /**
    * @return Personne[] Returns an array of Personne objects
    */
    public function statsPersonnesByAgeInterval($ageMin,$ageMax): array
    {
           $qb=$this->createQueryBuilder('p')
            ->select('avg(p.age) as ageMoyen,count(p.id) as nombrePersonne');
            $this->addIntervalAge($qb,$ageMin,$ageMax);
     return $qb->getQuery()
            ->getScalarResult()
        ;
    }

    private function addIntervalAge(QueryBuilder $qb,$ageMin,$ageMax)
    {
        $qb->andWhere('p.age >= :ageMin and p.age <= :ageMax')
       /*      ->setParameter('ageMin', $ageMin)
            ->setParameter('ageMax', $ageMax) */
        ->setParameters(['ageMin'=>$ageMin,'ageMax'=>$ageMax]);
       
    }

//    public function findOneBySomeField($value): ?Personne
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
