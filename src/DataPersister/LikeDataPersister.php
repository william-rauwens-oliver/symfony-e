<?php
namespace App\DataPersister;

use ApiPlatform\State\ProcessorInterface;
use App\Entity\Like;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LikeDataPersister implements ProcessorInterface
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
        if ($data instanceof Like) {
            $user = $this->tokenStorage->getToken()?->getUser();
            if ($user instanceof \App\Entity\User) {
                $data->setUser($user);
            } else {
                throw new \RuntimeException('Utilisateur non authentifié ou non trouvé');
            }
            // Vérifier si un like existe déjà pour ce couple user/publication
            $existing = $this->entityManager->getRepository(Like::class)->findOneBy([
                'user' => $user,
                'publication' => $data->getPublication(),
            ]);
            if ($existing) {
                return $existing;
            }
            try {
                $this->entityManager->persist($data);
                $this->entityManager->flush();
            } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                // Si un doublon survient malgré tout (race condition), on retourne le like existant
                $existing = $this->entityManager->getRepository(Like::class)->findOneBy([
                    'user' => $user,
                    'publication' => $data->getPublication(),
                ]);
                if ($existing) {
                    return $existing;
                }
                throw $e;
            }
        }
        return $data;
    }

    public function remove($data, array $context = []): void
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
} 