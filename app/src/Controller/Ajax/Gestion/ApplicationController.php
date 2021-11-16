<?php

namespace App\Controller\Ajax\Gestion;

use App\Entity\Application;
use App\Utils\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationController extends AbstractController
{
    /**
     * @Route("/ajax/gestion/applications/recherche", methods={"GET"}, name="ajax-gestion-applications-recherche")
     */
    public function rechercheApplication(Request $request): JsonResponse
    {
        // On récupère les données de la requête
        $dataRequest = $request->get('Saisie');
        $requete = $this->getDoctrine()
            ->getRepository(Application::class)
            ->rechercheParLibelle($dataRequest);
        $page = $request->get('page', 1);
        $maxResults = $request->get('max-results', Pagination::ELEMENTS_PAR_PAGE);
        if ($maxResults > 100) {
            $maxResults = 100;
        }

        // Construit un nouvel objet pagination à partir de cette requete
        $pagination = new Pagination($requete, $page, true, $maxResults);
        $resultats = $pagination->traitement();

        // Mise en forme du tableau des données (data transfert object)
        $donnees = [];
        $i = 0;
        foreach ($resultats['donnees'] as $resultat) {
            $donnees[$i]['Libelle'] = $resultat->getLabel();
            $donnees[$i]['Domaine'] = $resultat->getSousDomaine()->getDomaineParent()->getLabel();
            $donnees[$i]['SousDomaine'] = $resultat->getSousDomaine()->getLabel();
            $donnees[$i]['Exploitant'] = $resultat->getExploitant()->getLabel();
            $donnees[$i]['id'] = $resultat->getId();
            if ($resultat->getMOE() == null) {
                $donnees[$i]['MOE'] = '';
            } else {
                $donnees[$i]['MOE'] = $resultat->getMOE()->getLabel();
            }
            $i++;
        }
        $resultats['donnees'] = $donnees;

        // Demande à l'objet pagination de construire la réponse
        return new JsonResponse($resultats);
    }
}
