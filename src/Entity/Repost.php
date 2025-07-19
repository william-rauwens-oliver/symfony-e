<?php

namespace App\Entity;

use App\Repository\RepostRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ORM\Entity(repositoryClass: RepostRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_REPOST_USER_PUBLICATION', columns: ['user_id', 'publication_id'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['repost:read', 'publication:read']]),
        new Get(normalizationContext: ['groups' => ['repost:read', 'publication:read']]),
        new Post(
            processor: 'App\\DataPersister\\RepostDataPersister',
            security: "is_granted('IS_AUTHENTICATED_FULLY')"
        ),
        new Delete(security: "is_granted('IS_AUTHENTICATED_FULLY') and object.getUser() == user"),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['user' => 'exact'])]
class Repost
{
    #[Groups(['repost:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['repost:read', 'publication:read'])]
    #[ORM\ManyToOne(targetEntity: Publication::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Publication $publication = null;

    #[Groups(['repost:read', 'user:read'])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[Groups(['repost:read'])]
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['repost:read'])]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPublication(): ?Publication
    {
        return $this->publication;
    }
    public function setPublication(?Publication $publication): self
    {
        $this->publication = $publication;
        return $this;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }
    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function setCreatedAt(?\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
