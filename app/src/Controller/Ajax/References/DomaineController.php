<?php

namespace App\Controller\Ajax\References;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\References\Domaine;
use App\Form\References\DomaineType;

class DomaineController extends ReferenceController
{

    /**
     * @Route("/ajax/gestion/references/domaines", methods={"POST"}, name="ajax-reference-domaine-creer")
     */
    public function creer(Request $request): JsonResponse
    {
        // contrôle accès
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->creerReference($request, Domaine::class, DomaineType::class);
    }

    /**
     * @Route("/ajax/gestion/references/domaines/{reference}", methods={"PUT"}, name="ajax-reference-domaine-modifier")
     */
    public function modifier(Domaine $reference, Request $request): JsonResponse
    {
        // contrôle accès
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->getDoctrine()->getManager();
        if (!$reference->getSupprimeLe()) {
            $nouvelleReference = new Domaine();
            $form = $this->createForm(DomaineType::class, $nouvelleReference, ['method' => 'PUT']);
            $supprimeId = $reference->getId();
            $reference->setSupprimeLe(new \DateTime());
            $entityManager->flush();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // Instancie et persiste une nouvelle référence
                $nouvelleReference->setAjouteLe(new \DateTime());
                $entityManager->persist($nouvelleReference);
                foreach ($reference->getSousDomaines() as $sousDomaine) {
                    $sousDomaine->setDomaineParent($nouvelleReference);
                }
                $entityManager->flush();
                // modification ok
                return $this->retourneReponse(
                    Response::HTTP_CREATED,
                    true,
                    [],
                    [
                        'nouvelId'      => $nouvelleReference->getId(),
                        'supprimeId'   => $supprimeId,
                    ]
                );
            } else {
                $reference->setSupprimeLe(null);
                $entityManager->flush();
            }
            // formulaire non valide ou non soumis correctement
            return $this->retourneReponse(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                false,
                self::getErreurMessages($form)
            );
        } else {
            // tentative de modification d'une référence déjà supprimée
            return $this->retourneReponse(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                false,
                [
                    'Cette référence est déjà supprimée, impossible de la modifier !'
                ]
            );
        }
    }

    /**
     * @Route("/ajax/gestion/references/domaines/{reference}", methods={"DELETE"}, name="ajax-reference-domaine-supprimer")
     */
    public function supprimer(Domaine $reference): JsonResponse
    {
        // contrôle accès
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->getDoctrine()->getManager();
        $maintenant = new \DateTime();
        $suppression = false;
        $supprimeId = $reference->getId();
        foreach ($reference->getSousDomaines() as $sousDomaine) {
            if (!$sousDomaine->getSupprimeLe()) {
                $sousDomaine->setSupprimeLe($maintenant);
                $suppression = true;
            }
        }
        if (!$reference->getSupprimeLe()) {
            $reference->setSupprimeLe($maintenant);
            $suppression = true;
        }
        if ($suppression == true) {
            $entityManager->flush();
            return $this->retourneReponse(
                Response::HTTP_OK,
                true,
                [],
                [
                    'supprime_id' => $supprimeId
                ]
            );
        } else {
            return $this->retourneReponse(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                false,
                [
                    'Cette référence est déjà supprimée !'
                ]
            );
        }
    }
}
