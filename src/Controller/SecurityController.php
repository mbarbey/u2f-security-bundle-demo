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
use Samyoul\U2F\U2FServer\U2FException;
use App\EventSubscriber\U2fSubscriber;
use App\Service\U2fSecurity;

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

    public function u2fRegistration(Request $request, U2fSecurity $service)
    {
        $canRegister = $service->canRegister($this->getUser(), $request->getSchemeAndHttpHost());
        if ($canRegister->isAborted()) {
            return $this->redirectToRoute('user_list');
        }

        $registration = new U2fRegistration();
        $form = $this->createForm(U2fRegistrationType::class, $registration);

        $form->handleRequest($request);
        $error = null;
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $key = $service->validateRegistration($this->getUser(), $registration);
                $key->setName($registration->getName())
                    ->setUser($this->getUser());

                $em = $this->getDoctrine()->getManager();
                $em->persist($key);
                $em->flush();
                $request->getSession()->remove('registrationRequest');
                return $this->redirectToRoute('user_list');
            } catch (\Exception $e) {
                $error = [
                    'error' => $e
                ];
            }
        }

        $registrationData = $service->createRegistration($request->getSchemeAndHttpHost());

        return $this->render('security/u2fRegistration.html.twig', array(
            'jsRequest' => $registrationData['request'],
            'jsSignatures' => $registrationData['signatures'],
            'form' => $form->createView(),
            'error' => $error
        ));
    }

    public function u2fAuthentication(Request $request, U2fSecurity $service)
    {
        $authentication = new U2fAuthentication();
        $form = $this->createForm(U2fAuthenticationType::class, $authentication);

        $form->handleRequest($request);
        $error = null;
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $updatedKey = $service->validateAuthentication($this->getUser(), $authentication);

                $em = $this->getDoctrine()->getManager();
                $em->persist($updatedKey);
                $em->flush();

                if ($request->getSession()->has(U2fSubscriber::U2F_SECURITY_KEY)) {
                    $request->getSession()->remove(U2fSubscriber::U2F_SECURITY_KEY);
                }

                return $this->redirectToRoute('user_list');
            } catch (\Exception $e) {
                $error = [
                    'error' => $e
                ];
            }
        }

        $authenticationRequest = $service->createAuthentication($request->getSchemeAndHttpHost(), $this->getUser());

        return $this->render('security/u2fAuthentication.html.twig', array(
            'authenticationRequest' => $authenticationRequest,
            'form' => $form->createView(),
            'error' => $error
        ));
    }
}
