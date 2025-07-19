<?php

namespace App\Entity;

use App\Repository\FollowRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ORM\Entity(repositoryClass: FollowRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_follow', columns: ['follower_id', 'followed_id'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            processor: 'App\\DataPersister\\FollowDataPersister',
            security: "is_granted('IS_AUTHENTICATED_FULLY')"
        ),
        new Delete(
            processor: 'App\\DataPersister\\FollowDataPersister',
            security: "is_granted('IS_AUTHENTICATED_FULLY')"
        ),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['follower' => 'exact', 'followed' => 'exact'])]
class Follow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $follower = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $followed = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollower(): ?User
    {
        return $this->follower;
    }

    public function setFollower(?User $follower): static
    {
        $this->follower = $follower;
        return $this;
    }

    public function getFollowed(): ?User
    {
        return $this->followed;
    }

    public function setFollowed(?User $followed): static
    {
        $this->followed = $followed;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }
} 