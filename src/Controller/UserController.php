<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/app/dashboard', name: 'dashboard-user')]
    public function dashboard(UserRepository $userRepository): Response
    {

        $user = $this->getUser();

        $products = $user->getProducts();

        return $this->render(
            "app/dashboard.html.twig",
            [
                'products' => $products
            ]
        );
    }
}
