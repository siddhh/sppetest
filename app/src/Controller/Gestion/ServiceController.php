<?php

namespace App\Controller\Gestion;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Service;
use App\Entity\Application;
use App\Entity\User;
use App\Form\ServiceType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ServiceController extends AbstractController
{

    /**
     * @Route("/gestion/services", name="afficher-gestion-service-liste")
     */
    public function afficherGestionServiceListe(Request $request): Response
    {
        return $this->render('gestion/services/liste.html.twig');
    }

    /**
     * @Route("/gestion/service/creation", name="afficher-gestion-service-creation")
     */
    public function afficherGestionServiceCreation(Request $request, UrlGeneratorInterface $router): Response
    {
        $listeAplications = $this->getDoctrine()->getRepository(Application::class)->listeApplications();
        $listeUsers = $this->getDoctrine()->getRepository(User::class)->listeUsers();
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);
        $response = new Response();
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($service);
            $em->flush();
            $this->addFlash(
                'success',
                "Le service {$service->getLabel()} a été créé."
            );
            // redirige vers la liste des services
            return $this->redirectToRoute('afficher-gestion-service-liste');
        }

        return $this->render('gestion/services/creation.html.twig', [
            'form'                  => $form->createView(),
            'listeApplications'     => $listeAplications,
            'listeUsers'            => $listeUsers,
        ]);
    }

    /**
     * @Route("/gestion/service/{service}/modification", name="afficher-gestion-service-modification")
     * Permet de modifier un service existant
     */
    public function afficherGestionServiceModification(Request $request, Service $service): Response
    {
        $listeAplications = $this->getDoctrine()->getRepository(Application::class)->listeApplications();
        $listeUsers = $this->getDoctrine()->getRepository(User::class)->listeUsers();
        $form = $this->createForm(ServiceType::class, $service);

        // récupère les paramètres fournis par le service
        $form->handleRequest($request);
        // si valide, on persiste l'état du service en base de données
        if ($form->isSubmitted() && $form->isValid()) {
            $service = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($service);
            $entityManager->flush();
            $this->addFlash(
                'success',
                "Le service {$service->getLabel()} a bien été modifié."
            );
            // redirige vers la liste des services
            return $this->redirectToRoute('afficher-gestion-service-liste');
        }

        // Retourne la page web
        return $this->render('gestion/services/modification.html.twig', [
            'form'                  => $form->createView(),
            'service'               => $service,
            'listeApplications'     => $listeAplications,
            'listeUsers'            => $listeUsers,
        ]);
    }

    /**
     * @Route("/gestion/service/{service}/suppression", name="afficher-gestion-service-suppression")
     * Supprime le service
     */
    public function afficherGestionServiceSuppression(Service $service)
    {
        // Création de la date de suppression
        $service->setArchiveLe(new \DateTime());
        $em = $this->getDoctrine()->getManager();
        $em->persist($service);
        $em->flush();

        //flash de la suppression de service
        $this->addFlash(
            'success',
            "Le service {$service->getLabel()} a bien été supprimé."
        );
        // redirige vers la liste des services
        return $this->redirectToRoute('afficher-gestion-service-liste');
    }
}
