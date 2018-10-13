<?php

namespace App\Event\Authentication;

use Symfony\Component\EventDispatcher\Event;
use App\Model\User\U2fUserInterface;
use App\Event\U2fEvents;

class U2fPreAuthenticationEvent extends Event
{
    protected $user;

    protected $abort = false;

    public static function getName()
    {
        return U2fEvents::U2F_PRE_AUTHENTICATION;
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

    public function abort()
    {
        $this->abort = true;
        $this->stopPropagation();

        return $this;
    }

    public function isAborted()
    {
        return $this->abort;
    }
}