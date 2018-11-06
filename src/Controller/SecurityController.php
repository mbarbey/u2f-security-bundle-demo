<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use App\Form\U2fRegistrationType;
use App\Model\U2fRegistration;
use App\Model\U2fAuthentication;
use App\Form\U2fAuthenticationType;
use Mbarbey\U2fSecurityBundle\EventSubscriber\U2fSubscriber;
use Mbarbey\U2fSecurityBundle\Service\U2fSecurity;
use Symfony\Component\Form\FormError;
use App\Entity\Key;
use App\Repository\KeyRepository;

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

    public function u2fDelete(Request $request, $keyId,  KeyRepository $r)
    {
        $key = $r->find($keyId);

        if (!$key || !$this->getUser()->getU2fKeys()->contains($key)) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($key);
        $em->flush();

        return $this->redirectToRoute('user_details', ['userId' => $this->getUser()->getId()]);
    }

    public function u2fRegistration(Request $request, U2fSecurity $service)
    {
        /*
         * We check if there is any listener somewhere which have an objection for the current user registering a new key
         */
        $canRegister = $service->canRegister($this->getUser(), $request->getSchemeAndHttpHost());
        if ($canRegister->isAborted()) {
            $this->addFlash('warning', $canRegister->getReason());
            return $this->redirectToRoute('user_register_u2f_denied');
        }

        /*
         * The user is allowed to register a key :-)
         */
        $registration = new U2fRegistration();
        $form = $this->createForm(U2fRegistrationType::class, $registration);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $key = new Key();
                $key->setName($registration->getName())->setUser($this->getUser());
                $service->validateRegistration($this->getUser(), $registration, $key);

                $em = $this->getDoctrine()->getManager();
                $em->persist($key);
                $em->flush();

                return $this->redirectToRoute('user_details', ['userId' => $this->getUser()->getId()]);
            } catch (\Exception $e) {
                $form->get('name')->addError(new FormError($e->getMessage()));
            }
        }

        $registrationData = $service->createRegistration($request->getSchemeAndHttpHost());

        return $this->render('security/u2fRegistration.html.twig', array(
            'jsRequest' => $registrationData['request'],
            'jsSignatures' => $registrationData['signatures'],
            'form' => $form->createView(),
        ));
    }

    public function u2fRegistrationDenied()
    {
        return $this->render('security/u2fRegistrationDenied.html.twig');
    }

    public function u2fAuthentication(Request $request, U2fSecurity $service)
    {
        $canAuthenticate = $service->canAuthenticate($request->getSchemeAndHttpHost(), $this->getUser());
        if ($canAuthenticate->isAborted()) {
            $service->stopRequestingAuthentication();
            $this->addFlash('warning', 'Your connection was not secured by a security key');
            return $this->redirectToRoute('user_list');
        }

        $authentication = new U2fAuthentication();
        $form = $this->createForm(U2fAuthenticationType::class, $authentication);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
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
                $this->addFlash('danger', 'Authentication failed');
            }
        }

        $authenticationRequest = $service->createAuthentication($request->getSchemeAndHttpHost(), $this->getUser());

        return $this->render('security/u2fAuthentication.html.twig', array(
            'authenticationRequest' => $authenticationRequest,
            'form' => $form->createView()
        ));
    }
}
