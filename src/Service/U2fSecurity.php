<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Samyoul\U2F\U2FServer\U2FServer;
use Samyoul\U2F\U2FServer\U2FException;
use App\Entity\Key;
use App\Model\U2fRegistration\U2fRegistrationInterface;
use App\Model\User\U2fUserInterface;
use App\Model\U2fAuthentication\U2fAuthenticationInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Event\Authentication\U2fAuthenticationSuccessEvent;
use App\Event\Registration\U2fPreRegistrationEvent;
use App\Event\Registration\U2fRegistrationSuccessEvent;
use App\Event\Registration\U2fPostRegistrationEvent;
use App\Event\Registration\U2fRegistrationFailureEvent;

class U2fSecurity
{
    private $session;
    private $dispatcher;

    public function __construct(SessionInterface $session, EventDispatcherInterface $dispatcher)
    {
        $this->session = $session;
        $this->dispatcher = $dispatcher;
    }

    public function canRegister(U2fUserInterface $user, $appId = null)
    {
        $event = new U2fPreRegistrationEvent($user, $appId);
        $this->dispatcher->dispatch($event::getName(), $event);

        return $event;
    }

    public function createRegistration($appId)
    {
        $registrationData = U2FServer::makeRegistration($appId);
        $this->session->set('registrationRequest', $registrationData['request']);

        $jsRequest = $registrationData['request'];
        $jsSignatures = json_encode($registrationData['signatures']);

        return ['request' => $jsRequest, 'signatures' => $jsSignatures];
    }

    public function validateRegistration(U2fUserInterface $user, U2fRegistrationInterface $registration)
    {
        $u2fRequest = $this->session->get('registrationRequest');
        $u2fResponse = (object)json_decode($registration->getResponse(), true);

        try {
            $validatedRegistration = U2FServer::register($u2fRequest, $u2fResponse);
            foreach ($user->getU2fKeys() as $existingKey) {
                if ($existingKey->getCertificate() == $validatedRegistration->getCertificate()) {
                    throw new U2FException('Key already registered', 4);
                }
            }
        } catch (\Exception $e) {
            $this->dispatcher->dispatch(U2fRegistrationFailureEvent::getName(), new U2fRegistrationFailureEvent($user, $e));
            throw $e;
        }

        $key = new Key();
        $key->setCertificate($validatedRegistration->getCertificate());
        $key->setCounter($validatedRegistration->getCounter());
        $key->setKeyHandle($validatedRegistration->getKeyHandle());
        $key->setPublicKey($validatedRegistration->getPublicKey());

        $this->dispatcher->dispatch(U2fRegistrationSuccessEvent::getName(), new U2fRegistrationSuccessEvent($user, $key));

        $this->session->remove('registrationRequest');

        $this->dispatcher->dispatch(U2fPostRegistrationEvent::getName(), new U2fPostRegistrationEvent($user, $key));

        return $key;
    }

    public function createAuthentication($appId, U2fUserInterface $user)
    {
        $authenticationRequest = U2FServer::makeAuthentication($user->getU2fKeys()->toArray(), $appId);
        $this->session->set('authenticationRequest', $authenticationRequest);

        return [
            'appId' => $appId,
            'version' => U2FServer::VERSION,
            'challenge' => $authenticationRequest[0]->challenge(),
            'registeredKeys' => json_encode($authenticationRequest)
        ];
    }

    public function validateAuthentication(U2fUserInterface $user, U2fAuthenticationInterface $authentication)
    {
        $updatedKey = U2FServer::authenticate(
            $this->session->get('authenticationRequest'),
            $user->getU2fKeys()->toArray(),
            json_decode($authentication->getResponse())
        );

        $this->dispatcher->dispatch(U2fAuthenticationSuccessEvent::getName(), new U2fAuthenticationSuccessEvent($user, $updatedKey));

        $this->session->remove('authenticationRequest');

        return $updatedKey;
    }
}
