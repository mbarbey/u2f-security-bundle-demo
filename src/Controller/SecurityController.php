<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Samyoul\U2F\U2FServer\U2FServer;
use Symfony\Component\HttpFoundation\Request;
use App\Form\U2fRegistrationType;
use App\Model\U2fRegistration;
use App\Entity\Key;
use App\Model\U2fAuthentication;
use App\Form\U2fAuthenticationType;

class SecurityController extends AbstractController
{
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    public function u2fRegistration(Request $request)
    {
        $appId = $request->getScheme() . '://' . $request->getHttpHost();

        $registration = new U2fRegistration();
        $form = $this->createForm(U2fRegistrationType::class, $registration);

        $form->handleRequest($request);
        $error = null;
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $u2fRequest = $request->getSession()->get('registrationRequest');
                $u2fResponse = (object)json_decode($registration->getResponse(), true);

                $validatedRegistration = U2FServer::register($u2fRequest, $u2fResponse);
                $key = new Key();
                $key->setUser($this->getUser());
                $key->setCertificate($validatedRegistration->getCertificate());
                $key->setCounter($validatedRegistration->getCounter());
                $key->setKeyHandle($validatedRegistration->getKeyHandle());
                $key->setName($registration->getName());
                $key->setPublicKey($validatedRegistration->getPublicKey());

                $em = $this->getDoctrine()->getManager();
                $em->persist($key);
                $em->flush();
                $request->getSession()->remove('registrationRequest');
                return $this->redirectToRoute('user_list');
            } catch (\Exception $e) {
                $error = [
                    'request' => $u2fRequest,
                    'response' => $u2fResponse,
                    'error' => $e
                ];
            }
        } else {
            $registrationData = U2FServer::makeRegistration($appId);
            $request->getSession()->set('registrationRequest', $registrationData['request']);

            $jsRequest = $registrationData['request'];
            $jsSignatures = json_encode($registrationData['signatures']);
        }

        return $this->render('security/u2fRegistration.html.twig', array(
            'jsRequest' => $jsRequest,
            'jsSignatures' => $jsSignatures,
            'form' => $form->createView(),
            'error' => $error
        ));
    }

    public function u2fAuthentication(Request $request)
    {
        $appId = $request->getScheme() . '://' . $request->getHttpHost();

        $authentication = new U2fAuthentication();
        $form = $this->createForm(U2fAuthenticationType::class, $authentication);

        $form->handleRequest($request);
        $error = null;
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $validatedAuthentication = U2FServer::authenticate(
                    $request->getSession()->get('authenticationRequest'),
                    $this->getUser()->getU2fKeys()->toArray(),
                    json_decode($authentication->getResponse())
                );

                $em = $this->getDoctrine()->getManager();
                $em->persist($validatedAuthentication);
                $em->flush();

                return $this->redirectToRoute('user_list');
            } catch (\Exception $e) {
                $error = [
                    'request' => $request->getSession()->get('authenticationRequest'),
                    'response' => $authentication,
                    'error' => $e
                ];
            }
        } else {
            $authenticationRequest = U2FServer::makeAuthentication($this->getUser()->getU2fKeys()->toArray(), $appId);
            $request->getSession()->set('authenticationRequest', $authenticationRequest);
        }

        $authenticationRequest = [
            'appId' => $appId,
            'version' => U2FServer::VERSION,
            'challenge' => $authenticationRequest[0]->challenge(),
            'registeredKeys' => json_encode($authenticationRequest)
        ];

        return $this->render('security/u2fAuthentication.html.twig', array(
            'authenticationRequest' => $authenticationRequest,
            'form' => $form->createView(),
            'error' => $error
        ));
    }
}