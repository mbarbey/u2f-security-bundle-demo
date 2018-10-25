<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mbarbey\U2fSecurityBundle\Event\Authentication\U2fAuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Mbarbey\U2fSecurityBundle\Event\Authentication\U2fPreAuthenticationEvent;

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
            U2fAuthenticationFailureEvent::getName() => 'onU2fAuthenticationFailure',
            U2fPreAuthenticationEvent::getName() => 'onPreAuthentication'
        ];
    }

    public function onPreAuthentication(U2fPreAuthenticationEvent $event)
    {
        if ($this->session->get('u2f_registration_error_counter', 0) >= 3) {
            $this->flash->add('danger', "You failed to authenticate yourself and now you try to leave ? Mouahaha you are jailed here forever ! Or you can just logout and try again...");
        }
    }

    public function onU2fAuthenticationFailure(U2fAuthenticationFailureEvent $event)
    {
        if ($event->getFailureCounter() >= 3) {
            $this->flash->add('danger', "You have failed to fully authenticate 3 times or more. An email has been sent to the owner of this account for security purpose. (Just kidding, I am a demo, I don't send emails)");
        }
    }
}
