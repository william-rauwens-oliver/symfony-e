<?php

namespace App\Entity;

use App\Repository\CommentLikeRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Delete;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            processor: 'App\\DataPersister\\CommentLikeDataPersister',
            security: "is_granted('IS_AUTHENTICATED_FULLY')"
        ),
        new Delete(security: "is_granted('IS_AUTHENTICATED_FULLY') and object.getUser() == user"),
    ]
)]
#[ApiFilter(SearchFilter::class, properties: ['user' => 'exact', 'commentaire' => 'exact'])]
#[ORM\Entity(repositoryClass: CommentLikeRepository::class)]
#[ORM\Table(name: 'comment_like')]
#[ORM\UniqueConstraint(name: 'unique_comment_like', columns: ['user_id', 'commentaire_id'])]
class CommentLike
{
    #[Groups(['commentlike:read', 'commentaire:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['commentlike:read', 'commentaire:read'])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[Groups(['commentlike:read', 'commentaire:read', 'commentlike:write'])]
    #[ORM\ManyToOne(targetEntity: Commentaire::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Commentaire $commentaire = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCommentaire(): ?Commentaire
    {
        return $this->commentaire;
    }

    public function setCommentaire(?Commentaire $commentaire): self
    {
        $this->commentaire = $commentaire;
        return $this;
    }
}
