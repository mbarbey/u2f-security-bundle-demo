<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Form\UserType;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    public function index(UserRepository $r)
    {
        $users = $r->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    public function create(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('user_list');
        }

        return $this->render('user/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
