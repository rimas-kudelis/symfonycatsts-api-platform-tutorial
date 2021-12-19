<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Security;

class SetIsMeOnCurrentUserSubscriber implements EventSubscriberInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function onRequestEvent(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return;
        }

        $user->setIsMe(true);
    }

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => 'onRequestEvent',
        ];
    }
}
