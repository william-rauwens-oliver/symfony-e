<?php
namespace App\DataPersister;

use ApiPlatform\State\ProcessorInterface;
use App\Entity\Commentaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\Publication;
use ApiPlatform\Exception\InvalidArgumentException;

class CommentaireDataPersister implements ProcessorInterface
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
        if ($data instanceof Commentaire) {
            $user = $this->tokenStorage->getToken()?->getUser();
            if (is_object($user)) {
                $data->setUser($user);
            }
            // Vérification explicite de la publication
            $publication = $data->getPublication();
            if (!$publication || !$publication instanceof Publication) {
                throw new InvalidArgumentException('Publication invalide ou manquante pour le commentaire.');
            }
            if ($data->getCreatedAt() === null) {
                $data->setCreatedAt(new \DateTimeImmutable());
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