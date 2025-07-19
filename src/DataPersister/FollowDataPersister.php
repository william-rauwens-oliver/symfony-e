<?php

namespace App\DataPersister;

use ApiPlatform\State\ProcessorInterface;
use App\Entity\Follow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use ApiPlatform\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Delete;

class FollowDataPersister implements ProcessorInterface
{
    private $entityManager;
    private $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        
        if ($operation instanceof \ApiPlatform\Metadata\Delete) {
            // Pour la suppression, récupérer l'objet Follow existant
            $followId = $uriVariables['id'] ?? null;
            if (!$followId) {
                throw new InvalidArgumentException('ID du follow manquant.');
            }
            
            $follow = $this->entityManager->getRepository(Follow::class)->find($followId);
            if (!$follow) {
                throw new InvalidArgumentException('Follow non trouvé.');
            }
            
            // Vérifier que l'utilisateur connecté est bien le follower
            if ($follow->getFollower()->getId() !== $user->getId()) {
                throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Vous ne pouvez supprimer que vos propres follows.');
            }
            
            $this->entityManager->remove($follow);
            $this->entityManager->flush();
            return null;
        }
        
        if ($data instanceof Follow) {
            if (is_object($user)) {
                $data->setFollower($user);
            } else {
                throw new InvalidArgumentException('Utilisateur non authentifié.');
            }
            if (!$data->getFollowed()) {
                throw new InvalidArgumentException('Utilisateur à suivre manquant.');
            }
            
            // Vérifier si le follow existe déjà
            $existingFollow = $this->entityManager->getRepository(Follow::class)->findOneBy([
                'follower' => $user,
                'followed' => $data->getFollowed()
            ]);
            
            if ($existingFollow) {
                // Si le follow existe déjà, on le retourne sans créer de doublon
                return $existingFollow;
            }
            
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }
        return $data;
    }

    public function remove($data, array $context = []): void
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
} 