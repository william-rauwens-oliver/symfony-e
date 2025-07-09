<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Entity\Publication;
use App\Entity\Commentaire;
use App\Entity\Like;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation()
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');
        $user->setPassword('hashedpassword');

        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('hashedpassword', $user->getPassword());
    }

    public function testUserRoles()
    {
        $user = new User();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testUserIdentifier()
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $user->getUserIdentifier());
    }

    public function testUserPublications()
    {
        $user = new User();
        $publication = new Publication();
        $publication->setContent('Test publication');
        
        $user->addPublication($publication);
        $this->assertCount(1, $user->getPublications());
        $this->assertTrue($user->getPublications()->contains($publication));
        
        $user->removePublication($publication);
        $this->assertCount(0, $user->getPublications());
    }

    public function testUserCommentaires()
    {
        $user = new User();
        $commentaire = new Commentaire();
        $commentaire->setContent('Test comment');
        $commentaire->setUser($user);
        
        $this->assertEquals($user, $commentaire->getUser());
    }

    public function testUserLikes()
    {
        $user = new User();
        $like = new Like();
        $like->setUser($user);
        
        $this->assertEquals($user, $like->getUser());
    }
} 