<?php

namespace App\Entity;

use App\Repository\PublicationRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use App\Entity\PublicationInput;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\ApiSubresource;

#[ORM\Entity(repositoryClass: PublicationRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        // POST et PUT désactivés, gérés par des contrôleurs custom
        new Patch(security: "is_granted('IS_AUTHENTICATED_FULLY') and object.getUser() == user"),
        new Delete(security: "is_granted('IS_AUTHENTICATED_FULLY') and object.getUser() == user"),
    ],
    normalizationContext: ['groups' => ['publication:read']],
    denormalizationContext: ['groups' => ['publication:write']]
)]
#[ApiFilter(SearchFilter::class, properties: ['user' => 'exact'])]
class Publication
{
    #[Groups(['publication:read', 'repost:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['publication:read', 'repost:read'])]
    #[ORM\Column(type: 'text')]
    private ?string $texte = null;

    #[Groups(['publication:read', 'repost:read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $image = null;

    #[Groups(['publication:read', 'repost:read'])]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $video = null;

    #[Groups(['publication:read', 'repost:read', 'user:read'])]
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[\Symfony\Component\Serializer\Annotation\MaxDepth(1)]
    private ?User $user = null;

    #[Groups(['publication:read', 'repost:read'])]
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[Groups(['publication:read'])]
    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: Like::class)]
    #[\Symfony\Component\Serializer\Annotation\MaxDepth(1)]
    private Collection $likes;

    #[ORM\OneToMany(mappedBy: 'publication', targetEntity: Commentaire::class, orphanRemoval: true)]
    #[ApiSubresource]
    #[\Symfony\Component\Serializer\Annotation\MaxDepth(1)]
    private Collection $commentaires;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTexte(): ?string
    {
        return $this->texte;
    }

    public function setTexte(?string $texte): self
    {
        $this->texte = $texte;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getVideo(): ?string
    {
        return $this->video;
    }

    public function setVideo(?string $video): self
    {
        $this->video = $video;
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

    #[Groups(['publication:read'])]
    public function getLikeCount(): int
    {
        return $this->likes->count();
    }

    public function isLikedByUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        foreach ($this->likes as $like) {
            if ($like->getUser() === $user) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * @return Collection|Like[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    /**
     * @return Collection|Commentaire[]
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }
}
