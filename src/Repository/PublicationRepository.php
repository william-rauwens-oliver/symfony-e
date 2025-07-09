<?php

namespace App\Repository;

use App\Entity\Publication;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Publication>
 */
class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

    //    /**
    //     * @return Publication[] Returns an array of Publication objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Publication
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return Publication[] Returns an array of Publication objects with relations loaded
     */
    public function findAllWithRelations(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('p.likes', 'l')
            ->leftJoin('p.commentaires', 'c')
            ->leftJoin('c.user', 'cu')
            ->addSelect('u', 'l', 'c', 'cu')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByHashtag(string $tag): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where($qb->expr()->like('LOWER(p.content)', ':tag'));
        $qb->setParameter('tag', '%#' . strtolower($tag) . '%');
        $qb->orderBy('p.createdAt', 'DESC');
        return $qb->getQuery()->getResult();
    }
}
