<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ApiResource(
    normalizationContext: ['groups' => ['user:read']],
    operations: [
        new GetCollection(),
        new Get(),
    ]
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[Groups(['user:read', 'publication:read', 'commentaire:read', 'like:read', 'repost:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['user:read', 'publication:read', 'commentaire:read', 'like:read', 'repost:read'])]
    #[ORM\Column(length: 180)]
    #[\Symfony\Component\Serializer\Annotation\MaxDepth(1)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: false)]
    private ?string $password = null;

    #[Groups(['user:read', 'publication:read', 'commentaire:read', 'like:read', 'repost:read'])]
    #[ORM\Column(length: 180, nullable: true)]
    private ?string $username = null;

    #[Groups(['user:read'])]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Publication::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[\Symfony\Component\Serializer\Annotation\MaxDepth(1)]
    private iterable $publications;

    #[Groups(['user:read', 'repost:read'])]
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Repost::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[\Symfony\Component\Serializer\Annotation\MaxDepth(1)]
    private iterable $reposts;

    public function __construct()
    {
        $this->publications = new ArrayCollection();
        $this->reposts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    #[Groups(['user:read'])]
    public function getPublicationCount(): int
    {
        return is_iterable($this->publications) ? iterator_count($this->publications) : 0;
    }

    /**
     * @return iterable
     */
    public function getPublications(): iterable
    {
        return $this->publications;
    }

    /**
     * @param iterable $publications
     * @return self
     */
    public function setPublications(iterable $publications): self
    {
        $this->publications = $publications;
        return $this;
    }

    /**
     * @return iterable
     */
    public function getReposts(): iterable
    {
        return $this->reposts;
    }

    /**
     * @param iterable $reposts
     * @return self
     */
    public function setReposts(iterable $reposts): self
    {
        $this->reposts = $reposts;
        return $this;
    }
}
