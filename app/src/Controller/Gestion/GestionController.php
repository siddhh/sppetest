<?php

namespace App\Controller\Gestion;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GestionController extends AbstractController
{

    /**
     * @Route("/gestion", name="afficher-gestion-accueil")
     */
    public function afficherGestionAccueil(): Response
    {
        return $this->render('/gestion/accueil.html.twig');
    }
}
