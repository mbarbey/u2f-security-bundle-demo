<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UserType;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{
    public function index(UserRepository $r)
    {
        $users = $r->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    public function create(Request $request, UserPasswordEncoderInterface $encoder, UserRepository $r)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $r->findOneBy(['username' => $user->getUsername()]);

            if ($existing) {
                $this->addFlash('danger', 'This username is already taken.');
            } else {
                $password = $encoder->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', 'The account was successfully created.');

                return $this->redirectToRoute('login');
            }
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function details($userId, UserRepository $r)
    {
        $user = $r->find($userId);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        return $this->render('user/details.html.twig', [
            'user' => $user,
        ]);
    }

    public function toggleCanRegister()
    {
        $user = $this->getUser();

        $user->setCanRegisterKey(!$user->getCanRegisterKey());
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('user_list');
    }

    public function toggleCanAuthenticate()
    {
        $user = $this->getUser();

        $user->setCanAuthenticateKey(!$user->getCanAuthenticateKey());
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('user_list');
    }

    public function delete(Request $request, $userId, UserRepository $r, TokenStorageInterface $storage)
    {
        $user = $r->find($userId);

        if (!$user || $user->getId() != $this->getUser()->getId()) {
            throw $this->createNotFoundException();
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        $storage->setToken(null);

        $this->addFlash('success', 'The account was successfully deleted.');

        return $this->redirectToRoute('home');
    }
}
