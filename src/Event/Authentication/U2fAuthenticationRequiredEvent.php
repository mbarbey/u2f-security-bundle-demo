<?php

namespace App\Event\Authentication;

use Symfony\Component\EventDispatcher\Event;
use App\Model\User\U2fUserInterface;
use App\Event\U2fEvents;

class U2fAuthenticationRequiredEvent extends Event
{

    protected $user;

    public static function getName()
    {
        return U2fEvents::U2F_AUTHENTICATION_REQUIRED;
    }

    public function __construct(U2fUserInterface $user)
    {
        $this->setUser($user);
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(U2fUserInterface $user)
    {
        $this->user = $user;

        return $this;
    }
}