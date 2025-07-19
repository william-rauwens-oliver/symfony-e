<?php

namespace App\EventListener;

use App\Entity\Follow;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Symfony\Bundle\SecurityBundle\Security;

class FollowListener
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function prePersist(Follow $follow, PrePersistEventArgs $args): void
    {
        if ($follow->getFollower() === null) {
            $user = $this->security->getUser();
            if ($user) {
                $follow->setFollower($user);
            }
        }
    }
} 