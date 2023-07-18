<?php

namespace App\Controller;

use App\Service\Helpers;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/page')]
class StaticPageController extends AbstractController
{
    #[Route('/legal_notice', name: 'app_legal_notice')]    
    /**
     * Renvoie la vue de la page mentions légals
     *
     * @return Response
     */
    public function mentionsLegales(): Response
    {
        return $this->render('static_page/legal_notice.html.twig', [
           
        ]);
    }
    #[Route('/cookies_policy', name: 'app_cookies_policy')]    
    /**
     * Renvoie la vue de la politique des cookies
     *
     * @return Response
     */
    public function politiqueCookies(): Response
    {
        return $this->render('static_page/cookies_policy.html.twig', [
           
        ]);
    }
    #[Route('/about_us', name: 'app_about_us')]    
    /**
     * Renvoie la vue de la page A propos
     *
     * @return Response
     */
    public function aPropos(): Response
    {
        return $this->render('static_page/about_us.html.twig', [
          
        ]);
    }

    
   
}
