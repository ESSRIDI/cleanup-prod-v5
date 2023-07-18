<?php

namespace App\Controller;

use App\Service\Helpers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
     
    /**
     * Images des caroussels de la page d'accueil
     *
     * @return Response
     */
    public function index(): Response
    {
        $carousselImages = [];
        return $this->render('home/index.html.twig', [
            'carousselImages' =>$carousselImages,
        ]);
    }

    
}