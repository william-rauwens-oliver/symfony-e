<?php

namespace App\DataPersister;

use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use ApiPlatform\Exception\InvalidArgumentException;

class UserDataPersister implements ProcessorInterface
{
    private $entityManager;
    private $tokenStorage;
    private $passwordHasher;

    public function __construct(
        EntityManagerInterface $entityManager, 
        TokenStorageInterface $tokenStorage,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->passwordHasher = $passwordHasher;
    }

    public function process(mixed $data, \ApiPlatform\Metadata\Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof User && $operation instanceof \ApiPlatform\Metadata\Put) {
            // Debug logs
            error_log('DEBUG USER DATA PERSISTER - Operation: ' . get_class($operation));
            error_log('DEBUG USER DATA PERSISTER - URI Variables: ' . json_encode($uriVariables));
            error_log('DEBUG USER DATA PERSISTER - Data ID: ' . ($data->getId() ?? 'null'));
            
            // Récupérer l'ID depuis les variables d'URI
            $userId = $uriVariables['id'] ?? null;
            if (!$userId) {
                error_log('DEBUG USER DATA PERSISTER - No user ID in URI variables');
                throw new InvalidArgumentException('ID utilisateur manquant.');
            }

            error_log('DEBUG USER DATA PERSISTER - Looking for user with ID: ' . $userId);
            
            // Récupérer l'utilisateur existant
            $existingUser = $this->entityManager->getRepository(User::class)->find($userId);
            if (!$existingUser) {
                error_log('DEBUG USER DATA PERSISTER - User not found with ID: ' . $userId);
                throw new InvalidArgumentException('Utilisateur non trouvé.');
            }

            error_log('DEBUG USER DATA PERSISTER - User found: ' . $existingUser->getUsername());

            // Vérifier que l'utilisateur connecté modifie son propre profil
            $currentUser = $this->tokenStorage->getToken()?->getUser();
            if (!$currentUser || $currentUser->getId() !== $existingUser->getId()) {
                throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('Vous ne pouvez modifier que votre propre profil.');
            }

            // Mettre à jour les champs fournis
            if ($data->getEmail() !== null) {
                $existingUser->setEmail($data->getEmail());
            }

            if ($data->getUsername() !== null) {
                $existingUser->setUsername($data->getUsername());
            }

            // Gérer le mot de passe seulement s'il est fourni et non vide
            if ($data->getPassword() !== null && $data->getPassword() !== '') {
                $hashedPassword = $this->passwordHasher->hashPassword($existingUser, $data->getPassword());
                $existingUser->setPassword($hashedPassword);
            }
            // Si le mot de passe n'est pas fourni, on garde l'ancien

            $this->entityManager->persist($existingUser);
            $this->entityManager->flush();

            return $existingUser;
        }

        return $data;
    }
} 