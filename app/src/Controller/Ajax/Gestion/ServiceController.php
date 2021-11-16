<?php

namespace App\Controller\Ajax\Gestion;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Service;
use App\Utils\Pagination;
use Symfony\Component\HttpFoundation\Request;

class ServiceController extends AbstractController
{

    /**
     * @Route(
     *      "/ajax/gestion/services/recherche/{page?1}",
     *      methods={"GET"},
     *      name="ajax-gestion-services-recherche",
     *      requirements={"page"="\d+"}
     * )
     */
    public function listingServices(Request $request, int $page = 1): JsonResponse
    {
        $filtre = $request->get('filtre');

        $query = $this->getDoctrine()
            ->getRepository(Service::class)
            ->listeServicesFiltre($filtre);

        $pagination = new Pagination($query, $page);

        return new JsonResponse($pagination->traitement());
    }
}
