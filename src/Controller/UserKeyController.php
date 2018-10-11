<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\UserRepository;

class UserKeyController extends AbstractController
{
    public function index($userId, UserRepository $r)
    {
        $user = $r->find($userId);

        return $this->render('user/key/index.html.twig', [
            'user' => $user,
        ]);
    }
}
