<?php

namespace App\Controller\Gestion;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\References\DomaineType;
use App\Entity\References\Domaine;

class ReferenceController extends AbstractController
{
    /**
     * @Route("/gestion/references/domaines", name="afficher-gestion-reference-domaines")
     */
    public function afficherGestionDomaines(): Response
    {
        // contrôle accès
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        // récupère le formulaire correspondant à ce type de référence
        $form = $this->createForm(DomaineType::class);
        // récupère la liste des domaines et leurs sous-domaines
        $domainesList = $this->getDoctrine()->getRepository(Domaine::class)->liste();
        $referenceList = [];
        foreach ($domainesList as $domaine) {
            $referenceList[] = $domaine;
            $sousDomaines = $domaine->getSousDomaines()->getValues();
            usort($sousDomaines, function ($a, $b) {
                if ($a->getLabel() < $b->getLabel()) {
                    return -1;
                } else {
                    return 1;
                }
            });
            $referenceList = array_merge($referenceList, $sousDomaines);
        }
        // affiche la page de gestion des références
        return $this->render('gestion/references/domaines.html.twig', [
            'form'              => $form->createView(),
            'referenceList'     => $referenceList
        ]);
    }
}
