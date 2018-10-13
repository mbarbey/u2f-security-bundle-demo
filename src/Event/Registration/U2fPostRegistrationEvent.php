<?php

namespace App\Event\Registration;

use Symfony\Component\EventDispatcher\Event;
use App\Model\User\U2fUserInterface;
use App\Model\Key\U2fKeyInterface;
use App\Event\U2fEvents;

class U2fPostRegistrationEvent extends Event
{
    protected $user;

    protected $key;

    public static function getName()
    {
        return U2fEvents::U2F_POST_REGISTRATION;
    }

    public function __construct(U2fUserInterface $user, U2fKeyInterface $key)
    {
        $this->setUser($user);
        $this->setKey($key);
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

    public function getKey()
    {
        return $this->key;
    }

    public function setKey(U2fKeyInterface $key)
    {
        $this->key = $key;

        return $this;
    }
}
