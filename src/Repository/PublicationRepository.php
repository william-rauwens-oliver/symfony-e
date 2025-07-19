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

    /**
     * Recherche des publications par contenu (optimisée)
     */
    public function searchPublications(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->andWhere('p.texte LIKE :query OR u.username LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(50) // Limite pour éviter les problèmes de mémoire
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des publications par hashtag
     */
    public function findByHashtag(string $tag): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.texte LIKE :hashtag')
            ->setParameter('hashtag', '%#' . $tag . '%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des hashtags par nom (optimisée)
     */
    public function searchHashtags(string $query): array
    {
        // Recherche directe dans les publications contenant le hashtag
        $qb = $this->createQueryBuilder('p')
            ->select('p.texte')
            ->where('p.texte LIKE :hashtag')
            ->setParameter('hashtag', '%#' . $query . '%')
            ->setMaxResults(100); // Limite pour éviter les problèmes de mémoire

        $results = $qb->getQuery()->getResult();
        $hashtags = [];

        foreach ($results as $result) {
            preg_match_all('/#(\w+)/', $result['texte'], $matches);
            foreach ($matches[1] as $match) {
                $hashtag = strtolower($match);
                if (stripos($hashtag, strtolower($query)) !== false) {
                    $hashtags[] = $hashtag;
                }
            }
        }

        return array_unique($hashtags);
    }

    /**
     * Trouve les publications tendance (avec le plus de likes)
     */
    public function findTrendingPublications(int $limit = 10): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.likes', 'l')
            ->groupBy('p.id')
            ->orderBy('COUNT(l.id)', 'DESC')
            ->addOrderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve toutes les publications avec pagination
     */
    public function findAllWithPagination(int $page = 1, int $limit = 10): array
    {
        $offset = ($page - 1) * $limit;
        
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne tous les hashtags distincts trouvés dans les publications (limité)
     */
    public function findAllHashtags(int $limit = 50): array
    {
        $qb = $this->createQueryBuilder('p')
            ->select('p.texte')
            ->setMaxResults(500); // On prend plus large pour extraire plus de hashtags

        $results = $qb->getQuery()->getResult();
        $hashtags = [];
        foreach ($results as $result) {
            preg_match_all('/#(\w+)/', $result['texte'], $matches);
            foreach ($matches[1] as $match) {
                $hashtag = strtolower($match);
                $hashtags[] = $hashtag;
            }
        }
        $hashtags = array_unique($hashtags);
        return array_slice($hashtags, 0, $limit);
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
}
