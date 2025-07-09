<?php

namespace App\Tests\Service;

use App\Service\SuggestionService;
use App\Entity\User;
use App\Entity\Publication;
use App\Entity\Like;
use App\Entity\Commentaire;
use App\Repository\PublicationRepository;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class SuggestionServiceTest extends TestCase
{
    private SuggestionService $suggestionService;
    private PublicationRepository $publicationRepository;
    private FollowRepository $followRepository;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->publicationRepository = $this->createMock(PublicationRepository::class);
        $this->followRepository = $this->createMock(FollowRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        
        $this->suggestionService = new SuggestionService(
            $this->publicationRepository,
            $this->followRepository,
            $this->entityManager
        );
    }

    public function testSuggestPublicationsForUser()
    {
        // Créer des données de test
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');

        $publication1 = new Publication();
        $publication1->setContent('Test publication with #Symfony');
        $publication1->setUser($user);
        $publication1->setCreatedAt(new \DateTimeImmutable());

        $publication2 = new Publication();
        $publication2->setContent('Another publication');
        $publication2->setUser($user);
        $publication2->setCreatedAt(new \DateTimeImmutable());

        // Ajouter des likes et commentaires
        $like = new Like();
        $like->setUser($user);
        $like->setPublication($publication1);
        $publication1->addLike($like);

        $commentaire = new Commentaire();
        $commentaire->setUser($user);
        $commentaire->setPublication($publication1);
        $commentaire->setContent('Test comment');
        $publication1->addCommentaire($commentaire);

        $publications = [$publication1, $publication2];

        // Mock des repositories
        $this->publicationRepository->method('findAllWithRelations')
            ->willReturn($publications);

        $this->followRepository->method('findFollowing')
            ->willReturn([]);

        // Mock de l'EntityManager pour les requêtes DQL
        $queryBuilder = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $query = $this->createMock(\Doctrine\ORM\Query::class);
        
        $this->entityManager->method('createQueryBuilder')
            ->willReturn($queryBuilder);
        
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('from')->willReturnSelf();
        $queryBuilder->method('join')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('andWhere')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);
        $query->method('getResult')->willReturn([]);

        // Tester le service
        $suggestions = $this->suggestionService->suggestPublicationsForUser($user);

        // Vérifications
        $this->assertIsArray($suggestions);
        $this->assertCount(2, $suggestions);
        
        // La première publication devrait avoir un score plus élevé (likes + commentaires + hashtags)
        $this->assertGreaterThan($suggestions[1]['score'], $suggestions[0]['score']);
        
        // Vérifier que les scores sont calculés
        $this->assertArrayHasKey('score', $suggestions[0]);
        $this->assertArrayHasKey('scoreDetails', $suggestions[0]);
    }

    public function testExtractHashtags()
    {
        $reflection = new \ReflectionClass($this->suggestionService);
        $method = $reflection->getMethod('extractHashtags');
        $method->setAccessible(true);

        $content = 'This is a test with #Symfony and #PHP hashtags';
        $hashtags = $method->invoke($this->suggestionService, $content);

        $this->assertContains('Symfony', $hashtags);
        $this->assertContains('PHP', $hashtags);
        $this->assertCount(2, $hashtags);
    }

    public function testExtractHashtagsEmpty()
    {
        $reflection = new \ReflectionClass($this->suggestionService);
        $method = $reflection->getMethod('extractHashtags');
        $method->setAccessible(true);

        $hashtags = $method->invoke($this->suggestionService, 'No hashtags here');
        $this->assertEmpty($hashtags);
    }

    public function testExtractHashtagsNull()
    {
        $reflection = new \ReflectionClass($this->suggestionService);
        $method = $reflection->getMethod('extractHashtags');
        $method->setAccessible(true);

        $hashtags = $method->invoke($this->suggestionService, null);
        $this->assertEmpty($hashtags);
    }
} 