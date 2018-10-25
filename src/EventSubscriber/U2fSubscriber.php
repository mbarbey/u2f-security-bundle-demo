<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mbarbey\U2fSecurityBundle\Event\Authentication\U2fAuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class U2fSubscriber implements EventSubscriberInterface
{
    private $flash;
    private $session;

    public function __construct(FlashBagInterface $flash, SessionInterface $session)
    {
        $this->flash = $flash;
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return [
            U2fAuthenticationFailureEvent::getName() => 'onU2fAuthenticationFailure'
        ];
    }

    public function onU2fAuthenticationFailure(U2fAuthenticationFailureEvent $event)
    {
        if ($event->getFailureCounter() >= 3) {
            $this->flash->add('danger', "You have failed to fully authenticate 3 times or more. An email has been sent to the owner of this account for security purpose. (Just kidding, I am a demo, I don't send emails)");
        }
    }
}
