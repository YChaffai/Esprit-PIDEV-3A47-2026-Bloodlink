<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontHomeController extends AbstractController
{
    #[Route('/front', name: 'front_home')]
    public function index(): Response
    {
        return $this->render('front/home.html.twig');
    }
}
