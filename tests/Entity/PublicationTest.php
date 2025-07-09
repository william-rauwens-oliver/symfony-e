<?php

namespace App\Tests\Entity;

use App\Entity\Publication;
use App\Entity\User;
use App\Entity\Commentaire;
use App\Entity\Like;
use PHPUnit\Framework\TestCase;

class PublicationTest extends TestCase
{
    public function testPublicationCreation()
    {
        $publication = new Publication();
        $publication->setContent('Test publication content');
        $publication->setCreatedAt(new \DateTimeImmutable());

        $this->assertEquals('Test publication content', $publication->getContent());
        $this->assertInstanceOf(\DateTimeImmutable::class, $publication->getCreatedAt());
    }

    public function testPublicationUser()
    {
        $publication = new Publication();
        $user = new User();
        $user->setUsername('testuser');
        
        $publication->setUser($user);
        $this->assertEquals($user, $publication->getUser());
    }

    public function testPublicationCommentaires()
    {
        $publication = new Publication();
        $commentaire = new Commentaire();
        $commentaire->setContent('Test comment');
        
        $publication->addCommentaire($commentaire);
        $this->assertCount(1, $publication->getCommentaires());
        $this->assertTrue($publication->getCommentaires()->contains($commentaire));
        
        $publication->removeCommentaire($commentaire);
        $this->assertCount(0, $publication->getCommentaires());
    }

    public function testPublicationLikes()
    {
        $publication = new Publication();
        $like = new Like();
        
        $publication->addLike($like);
        $this->assertCount(1, $publication->getLikes());
        $this->assertTrue($publication->getLikes()->contains($like));
        
        $publication->removeLike($like);
        $this->assertCount(0, $publication->getLikes());
    }

    public function testPublicationHashtags()
    {
        $publication = new Publication();
        $publication->setContent('This is a test with #Symfony and #PHP hashtags');
        
        $hashtags = $publication->getHashtags();
        $this->assertContains('Symfony', $hashtags);
        $this->assertContains('PHP', $hashtags);
        $this->assertCount(2, $hashtags);
    }

    public function testPublicationNoHashtags()
    {
        $publication = new Publication();
        $publication->setContent('This is a test without hashtags');
        
        $hashtags = $publication->getHashtags();
        $this->assertEmpty($hashtags);
    }

    public function testPublicationNullContent()
    {
        $publication = new Publication();
        // setContent n'accepte que des strings, on teste avec une chaîne vide
        $publication->setContent('');
        
        $hashtags = $publication->getHashtags();
        $this->assertEmpty($hashtags);
    }
} 