<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthentificationController extends AbstractController
{

    /**
     * @Route("/connexion", name="app-connexion")
     */
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        // Un utilisateur déjà connecté n'a pas besoin d'être authentifié à nouveau, il est redirigé vers la page d'accueil ou tableau de bord
        if ($this->getUser()) {
            return $this->redirectToRoute('afficher-tableau-de-bord');
        }

        // Affiche le formulaire de connexion
        return $this->render('authentification/connexion.html.twig', [
            'dernier_login'             => $authenticationUtils->getLastUsername(),             // Dernier login utilisé lors de la derniere authentification
            'erreurAuthentification'    => $authenticationUtils->getLastAuthenticationError(),  // Exception retournée en cas d'erreur lors de l'authentification
        ]);
    }

    /**
     * @Route("/deconnexion", name="app-deconnexion")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
