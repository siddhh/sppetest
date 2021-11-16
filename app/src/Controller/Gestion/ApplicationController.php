<?php

namespace App\Controller\Gestion;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ApplicationType;
use App\Entity\Application;
use App\Entity\References\Domaine;

class ApplicationController extends AbstractController
{

    /**
     * @Route("/gestion/applications", name="afficher-gestion-application-liste")
     */
    public function afficherGestionApplicationListe(Request $request): Response
    {
        return $this->render('gestion/applications/liste.html.twig');
    }

    /**
     * @Route("/gestion/application/creation", name="afficher-gestion-application-creation")
     */
    public function afficherGestionApplicationCreation(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $listeDomaines = $this->getDoctrine()->getRepository(Domaine::class)->domainesParentsNonSupprimes();
        $application = new Application();
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $application = $form->getData();
            $labelMajuscules = strtoupper($application->getLabel());
            $application->setLabel($labelMajuscules);
            $em->persist($application);
            $em->flush();
            $this->addFlash(
                'success',
                'Votre création d\'application est bien enregistrée !'
            );
            return $this->redirectToRoute("afficher-gestion-application-liste");
        }
        return $this->render(
            'gestion/applications/creation.html.twig',
            [
                'titre' => 'Création',
                'form' => $form->createView(),
                'domaines' => $listeDomaines,
            ]
        );
    }

    /**
     * @Route(
     *      "/gestion/application/{id}/modification",
     *      name="afficher-gestion-application-modification",
     *      requirements={"id"="\d+"}
     * )
     */
    public function afficherGestionApplicationModification(Request $request, Application $application): Response
    {
        $em = $this->getDoctrine()->getManager();
        $listeDomaines = $this->getDoctrine()->getRepository(Domaine::class)->domainesParentsNonSupprimes();
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->request->get("application")["sousDomaine"] != '') {
            $application = $form->getData();
            $labelMajuscules = strtoupper($application->getLabel());
            $application->setLabel($labelMajuscules);
            $em->persist($application);
            $em->flush();
            $this->addFlash(
                'success',
                'Votre modification d\'application est bien enregistrée !'
            );
            return $this->redirectToRoute("afficher-gestion-application-liste");
        }

        return $this->render(
            'gestion/applications/modification.html.twig',
            [
                'titre' => 'Modification',
                'form' => $form->createView(),
                'domaines' => $listeDomaines,
            ]
        );
    }

    /**
     * @Route(
     *      "/gestion/application/{id}/suppression",
     *      name="afficher-gestion-application-suppression",
     *      requirements={"id"="\d+"}
     * )
     */
    public function afficherGestionSuppressionApplication(Request $request, Application $application): Response
    {
        $application->setSupprimeLe(new \DateTime('NOW'));
        $em = $this->getDoctrine()->getManager();
        $em->persist($application);
        $em->flush();
        $this->addFlash(
            'success',
            'L\'application est désormais supprimée !'
        );
        return $this->redirectToRoute("afficher-gestion-application-liste");
    }
}
