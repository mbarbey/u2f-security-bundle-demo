<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Mbarbey\U2fSecurityBundle\Event\Authentication\U2fAuthenticationFailureEvent;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Mbarbey\U2fSecurityBundle\Event\Registration\U2fPreRegistrationEvent;
use App\Entity\User;
use Mbarbey\U2fSecurityBundle\Event\Authentication\U2fPreAuthenticationEvent;

class U2fSubscriber implements EventSubscriberInterface
{
    private $flash;
    private $session;
    private $router;

    public function __construct(FlashBagInterface $flash, SessionInterface $session, RouterInterface $router)
    {
        $this->flash = $flash;
        $this->session = $session;
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            U2fAuthenticationFailureEvent::getName() => 'onU2fAuthenticationFailure',
            KernelEvents::REQUEST => 'onKernelRequest',
            U2fPreRegistrationEvent::getName() => 'onU2fPreRegistration',
            U2fPreAuthenticationEvent::getName() => 'onU2fPreAuthentication'
        ];
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (
            $event->isMasterRequest() &&
            $event->getRequest()->getSession()->get('u2f_registration_error_counter', 0) > 0
        ) {
            $route = $event->getRequest()->get('_route');
            $whitelist = ['login', 'logout', 'user_authenticate_u2f'];
            if (!in_array($route, $whitelist) && substr($route, 0, 1) != '_') {
                $this->flash->add('warning', "Mouahaha, you are not allowed to leave ! Except if you successfully authenticate or if you logout...");
            }
        }
    }

    public function onU2fAuthenticationFailure(U2fAuthenticationFailureEvent $event)
    {
        if ($event->getFailureCounter() >= 3) {
            $this->flash->add('danger', "You have failed to fully authenticate 3 times or more. An email has been sent to the owner of this account for security purpose. (Just kidding, I am a demo, I don't send emails)");
        }
    }

    public function onU2fPreRegistration(U2fPreRegistrationEvent $event)
    {
        $user = $event->getUser();

        /*
         * Here we simply deny the registration of a key based on a property of the user. In real case, you will use
         * some more complex conditions but the logic is still the same.
         */

        /** @var $user \App\Entity\User */
        if ($user instanceof User && !$user->getCanRegisterKey()) {
            $event->abort('You shall not pass !');
        }
    }

    public function onU2fPreAuthentication(U2fPreAuthenticationEvent $event)
    {
        $user = $event->getUser();

        /*
         * Here we simply deny the registration of a key based on a property of the user. In real case, you will use
         * some more complex conditions but the logic is still the same.
         */

        /** @var $user \App\Entity\User */
        if ($user instanceof User && !$user->getCanAuthenticateKey()) {
            $event->abort();
        }
    }
}
