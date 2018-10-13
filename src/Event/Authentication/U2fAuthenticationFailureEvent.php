<?php

namespace App\Event\Authentication;

use Symfony\Component\EventDispatcher\Event;
use App\Model\User\U2fUserInterface;
use App\Model\Key\U2fKeyInterface;
use App\Event\U2fEvents;

class U2fAuthenticationFailureEvent extends Event
{
    protected $user;

    protected $error;

    protected $key;

    protected $failureCounter;

    public static function getName()
    {
        return U2fEvents::U2F_AUTHENTICATION_FAILURE;
    }

    public function __construct(U2fUserInterface $user, string $error, int $failureCounter = 1, U2fKeyInterface $key = null)
    {
        $this->setUser($user);
        $this->setError($error);
        $this->setFailureCounter($failureCounter);
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

    public function getError()
    {
        return $this->error;
    }

    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey(U2fKeyInterface $key = null)
    {
        $this->key = $key;

        return $this;
    }

    public function getFailureCounter()
    {
        return $this->failureCounter;
    }

    public function setFailureCounter(int $failureCounter)
    {
        $this->failureCounter = $failureCounter;

        return $this;
    }
}
