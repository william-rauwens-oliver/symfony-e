<?php

namespace App\EventListener;

use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LogoutListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();
        
        if ($session) {
            $session->getFlashBag()->add('info', 'Vous avez été déconnecté avec succès.');
        }
    }
} 