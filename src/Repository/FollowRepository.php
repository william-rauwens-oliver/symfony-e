<?php

namespace App\Repository;

use App\Entity\Follow;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Follow>
 *
 * @method Follow|null find($id, $lockMode = null, $lockVersion = null)
 * @method Follow|null findOneBy(array $criteria, array $orderBy = null)
 * @method Follow[]    findAll()
 * @method Follow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FollowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Follow::class);
    }

    public function findFollowers(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.followed = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function findFollowing(User $user): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.follower = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    public function isFollowing(User $follower, User $followed): bool
    {
        $follow = $this->findOneBy([
            'follower' => $follower,
            'followed' => $followed
        ]);
        return $follow !== null;
    }
} 