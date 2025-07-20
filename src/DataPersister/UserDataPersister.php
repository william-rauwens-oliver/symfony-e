<?php

namespace App\DataPersister;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserDataPersister implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenStorageInterface $tokenStorage
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        if ($operation instanceof \ApiPlatform\Metadata\Delete) {
            $this->deleteUser($data);
        }
    }

    private function deleteUser(User $user): void
    {
        // 1. Supprimer tous les likes de l'utilisateur
        $this->entityManager->createQueryBuilder()
            ->delete('App\Entity\Like', 'l')
            ->where('l.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        // 2. Supprimer tous les likes de commentaires de l'utilisateur
        $this->entityManager->createQueryBuilder()
            ->delete('App\Entity\CommentLike', 'cl')
            ->where('cl.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        // 3. Supprimer tous les follows où l'utilisateur est follower
        $this->entityManager->createQueryBuilder()
            ->delete('App\Entity\Follow', 'f')
            ->where('f.follower = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        // 4. Supprimer tous les follows où l'utilisateur est followed
        $this->entityManager->createQueryBuilder()
            ->delete('App\Entity\Follow', 'f')
            ->where('f.followed = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        // 5. Supprimer tous les commentaires de l'utilisateur
        $this->entityManager->createQueryBuilder()
            ->delete('App\Entity\Commentaire', 'c')
            ->where('c.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        // 6. Supprimer tous les reposts de l'utilisateur
        $this->entityManager->createQueryBuilder()
            ->delete('App\Entity\Repost', 'r')
            ->where('r.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        // 7. Supprimer toutes les publications de l'utilisateur
        // (cela supprimera automatiquement les likes et commentaires associés grâce aux cascades)
        $this->entityManager->createQueryBuilder()
            ->delete('App\Entity\Publication', 'p')
            ->where('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();

        // 8. Supprimer l'utilisateur lui-même
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        // 9. Invalider la session
        $this->tokenStorage->setToken(null);
    }
} 