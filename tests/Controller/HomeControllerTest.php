<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\Publication;
use App\Repository\UserRepository;
use App\Repository\PublicationRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;

class HomeControllerTest extends WebTestCase
{
    public function testHomePageAccess()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Accueil');
    }

    public function testHomePageWithAuthenticatedUser()
    {
        $client = static::createClient();
        
        // Simuler un utilisateur connecté
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('test@example.com');
        
        if (!$testUser) {
            // Créer un utilisateur de test si nécessaire
            $testUser = new User();
            $testUser->setUsername('testuser');
            $testUser->setEmail('test@example.com');
            $testUser->setPassword('hashedpassword');
            
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $entityManager->persist($testUser);
            $entityManager->flush();
        }
        
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.publication-form');
    }

    public function testCreatePublication()
    {
        $client = static::createClient();
        
        // Simuler un utilisateur connecté
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('test@example.com');
        
        if (!$testUser) {
            $testUser = new User();
            $testUser->setUsername('testuser');
            $testUser->setEmail('test@example.com');
            $testUser->setPassword('hashedpassword');
            
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $entityManager->persist($testUser);
            $entityManager->flush();
        }
        
        $client->loginUser($testUser);
        
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();

        // Soumettre une publication via le formulaire nommé 'publication'
        $form = $crawler->filter('form[name=publication]')->form([
            'publication[content]' => 'Test publication content'
        ]);
        $client->submit($form);

        $this->assertTrue(
            $client->getResponse()->isRedirect('/') || $client->getResponse()->isRedirect() || $client->getResponse()->isSuccessful(),
            'La réponse devrait être une redirection ou un succès.'
        );
        
        // Vérifier que la publication a été créée
        $publicationRepository = static::getContainer()->get(PublicationRepository::class);
        $publication = $publicationRepository->findOneBy(['content' => 'Test publication content']);
        $this->assertNotNull($publication);
        $this->assertEquals($testUser, $publication->getUser());
    }

    public function testSearchPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/search?q=test');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Résultats pour "test"');
    }

    public function testHashtagPage()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/hashtag/symfony');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', '#symfony');
    }

    public function testSuggestedPageWithoutAuth()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/suggested');

        // Devrait rediriger vers la page de connexion
        $this->assertResponseRedirects('/login');
    }

    public function testSuggestedPageWithAuth()
    {
        $client = static::createClient();
        
        // Simuler un utilisateur connecté
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('test@example.com');
        
        if (!$testUser) {
            $testUser = new User();
            $testUser->setUsername('testuser');
            $testUser->setEmail('test@example.com');
            $testUser->setPassword('hashedpassword');
            
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $entityManager->persist($testUser);
            $entityManager->flush();
        }
        
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/suggested');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Suggestions personnalisées');
    }
} 