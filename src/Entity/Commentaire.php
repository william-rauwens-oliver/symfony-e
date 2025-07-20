<?php

namespace App\Entity;

use App\Repository\CommentaireRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiSubresource;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            processor: 'App\\DataPersister\\CommentaireDataPersister',
            security: "is_granted('IS_AUTHENTICATED_FULLY')"
        ),
        new Put(security: "is_granted('IS_AUTHENTICATED_FULLY') and object.getUser() == user"),
        new Patch(security: "is_granted('IS_AUTHENTICATED_FULLY') and object.getUser() == user"),
        new Delete(security: "is_granted('IS_AUTHENTICATED_FULLY') and object.getUser() == user"),
    ]
)]
#[ORM\Entity(repositoryClass: CommentaireRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['publication' => 'exact', 'parent' => 'exact'])]
class Commentaire
{
    #[Groups(['commentaire:read', 'publication:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['commentaire:read', 'publication:read'])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[Groups(['commentaire:read', 'publication:read'])]
    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Groups(['commentaire:read', 'publication:read', 'user:read'])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[Groups(['commentaire:read', 'commentaire:write', 'publication:read'])]
    #[ORM\ManyToOne(targetEntity: Publication::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Publication $publication = null;

    #[Groups(['commentaire:read', 'commentaire:write', 'publication:read'])]
    #[ORM\ManyToOne(targetEntity: Commentaire::class, inversedBy: 'replies')]
    #[\Symfony\Component\Serializer\Annotation\MaxDepth(1)]
    private ?Commentaire $parent = null;

    #[Groups(['commentaire:read'])]
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Commentaire::class)]
    #[\Symfony\Component\Serializer\Annotation\MaxDepth(2)]
    private iterable $replies;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'commentaire', targetEntity: CommentLike::class)]
    #[Groups(['commentaire:read'])]
    private iterable $commentLikes;

    #[Groups(['commentaire:read'])]
    public function getLikeCount(): int
    {
        return is_iterable($this->commentLikes) ? iterator_count($this->commentLikes) : 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
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

    public function getParent(): ?Commentaire
    {
        return $this->parent;
    }
    public function setParent(?Commentaire $parent): self
    {
        $this->parent = $parent;
        return $this;
    }
    public function getReplies(): iterable
    {
        return $this->replies;
    }
}
