<?php

namespace App\Controller\Ajax\Gestion;

use App\Entity\User;
use App\Utils\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{

    /**
     * @Route("/ajax/gestion/utilisateurs/recherche", methods={"GET"}, name="ajax-gestion-utilisateurs-recherche")
     */
    public function afficherGestionUtilisateurListe(Request $request): JsonResponse
    {
        // Récupère les paramètres de filtrage
        $filters = [];
        foreach (['search', 'balp', 'supprimeLe'] as $parameterName) {
            $value =  $request->query->get($parameterName);
            if (null !== $value) {
                $filters[$parameterName] = $value;
            }
        }
        // et le "pointeur" de pagination
        $page = $request->query->get('page', 1);
        $maxResults = $request->query->get('max-results', Pagination::ELEMENTS_PAR_PAGE);
        if ($maxResults > 100) {
            $maxResults = 100;
        }

        // Récupère la requète permettant de chercher les utilisateurs avec les filtres demandés
        $query = $this->getDoctrine()->getRepository(User::class)
            ->getQuerySearchUserIds($filters);

        // Construit un nouvel objet pagination à partir de cette requete
        $pagination = new Pagination($query, $page, false, $maxResults);
        $resultat = $pagination->traitement();
        $userIds = array_map(
            function ($e) {
                return $e['id'];
            },
            $resultat['donnees']
        );
        $resultat['donnees'] = $this->getDoctrine()->getRepository(User::class)->createQueryBuilder('u')
            ->select('partial u.{id, login, balp, nom, prenom}, partial s.{id, label}')
            ->leftJoin('u.services', 's')
            ->where('u.id IN(:userIds)')->setParameter('userIds', $userIds)
            ->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC')
            ->getQuery()
            ->getArrayResult();

        // Demande à l'objet pagination de construire la réponse
        return new JsonResponse($resultat);
    }
}
