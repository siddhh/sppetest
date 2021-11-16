<?php

namespace App\Controller\Gestion;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{

    /**
     * Listing / Recherche d'utilisateurs
     * @Route("/gestion/utilisateurs", name="afficher-gestion-utilisateur-liste")
     */
    public function afficherGestionUtilisateurListe(Request $request): Response
    {
        return $this->render('gestion/utilisateurs/liste.html.twig');
    }

    /**
     * Création d'un nouvel utilisateur
     * @Route("/gestion/utilisateur/creation", name="afficher-gestion-utilisateur-creation")
     */
    public function afficherGestionUtilisateurCreation(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(UserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère et normalize l'utilisateur récupéré
            $user = $form->getData();
            $user->normalize();
            // Recherche si cet utilisateur n'existe pas déjà (même login, même balp, et non supprimé)
            $em = $this->getDoctrine()->getManager();
            if (null !== ($tmpUser = $em->getRepository(User::class)->getUserByBalp($user->getBalp()))) {
                $errorMessage = "Un compte utilisateur ({$tmpUser->getPrenom()} {$tmpUser->getNom()} <{$tmpUser->getBalp()}>) associé à cette adresse mail existe déjà dans SPPE. Impossible de créer cet utilisateur.";
                $this->addFlash('danger', $errorMessage);
            } elseif (null !== ($tmpUser = $em->getRepository(User::class)->getUserByLogin($user->getLogin()))) {
                $errorMessage = "Un utilisateur avec ce login ({$tmpUser->getPrenom()} {$tmpUser->getNom()} <{$tmpUser->getBalp()}>) existe déjà. Impossible de créer cet utilisateur.";
                $this->addFlash('danger', $errorMessage);
            } else {
                // Hash le nouveau mot de passe si nécessaire
                if (true === $form->get('motdepasseUpdate')->getData()) {
                    if (null !== ($clearPassword = $form->get('motdepasseDisplayed')->getData())) {
                        $user->setMotdepasse($passwordHasher->hashPassword($user, $clearPassword));
                    } else {
                        $user->setMotdepasse(null);
                    }
                }
                // Fait persister l'objet en base de données
                $em->persist($user);
                $em->flush();
                // Redirige vers la liste des utilisateur
                $this->addFlash('success', "Utilisateur {$user->getPrenom()} {$user->getNom()} créé.");
                return $this->redirectToRoute('afficher-gestion-utilisateur-liste');
            }
        }

        return $this->render('gestion/utilisateurs/edition.html.twig', [
            'form'  => $form->createView(),
        ]);
    }

    /**
     * Modification d'un utilisateur 
     * @Route("/gestion/utilisateur/{user}/modification", name="afficher-gestion-utilisateur-modification")
     */
    public function afficherGestionUtilisateurModification(User $user, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        // Si le bouton de suppression a été cliqué
        if ($form->get('actionRemove')->isClicked()) {
            // On vide le cache du manager pour le forcer à recharger l'objet user à partir de la base de données (il peut avoir été affecté suite à une modification des champs du formulaire)
            $em->clear();
            $user = $em->getRepository(User::class)->find($user->getId());
            // Puis on affecte le flag et on enregistre...
            $user->setSupprimeLe(new \DateTime());
            $em->flush();
            $this->addFlash('success', "Utilisateur {$user->getPrenom()} {$user->getNom()} a été supprimé.");
            return $this->redirectToRoute('afficher-gestion-utilisateur-liste');
        } elseif ($form->isSubmitted() && $form->isValid()) {
            // Récupère et normalize l'utilisateur récupéré
            $user = $form->getData();
            $user->normalize();
            // Recherche si cet utilisateur n'existe pas déjà (même login, même balp et non supprimé)
            $tmpUser = $em->getRepository(User::class)->getUserByLogin($user->getLogin());
            if (null !== ($tmpUser = $em->getRepository(User::class)->getUserByBalp($user->getBalp())) && $tmpUser->getId() !== $user->getId()) {
                $errorMessage = "Un compte utilisateur ({$tmpUser->getPrenom()} {$tmpUser->getNom()} <{$tmpUser->getBalp()}>) associé à cette adresse mail existe déjà dans SPPE. Impossible de modifier cet utilisateur.";
                $this->addFlash('danger', $errorMessage);
            } elseif (null !== ($tmpUser = $em->getRepository(User::class)->getUserByLogin($user->getLogin())) && $tmpUser->getId() !== $user->getId()) {
                $errorMessage = "Un utilisateur {$tmpUser->getPrenom()} {$tmpUser->getNom()} <{$tmpUser->getBalp()}> existe déjà avec ce login. Impossible de modifier cet utilisateur.";
                $this->addFlash('danger', $errorMessage);
            } else {
                // Hash le nouveau mot de passe si nécessaire
                if (true === $form->get('motdepasseUpdate')->getData()) {
                    if (null !== ($clearPassword = $form->get('motdepasseDisplayed')->getData())) {
                        $user->setMotdepasse($passwordHasher->hashPassword($user, $clearPassword));
                    } else {
                        $user->setMotdepasse(null);
                    }
                }
                // Enregistre les modifications en base de données
                $em->flush();
                $this->addFlash('success', "Utilisateur {$user->getPrenom()} {$user->getNom()} mis à jour.");
                return $this->redirectToRoute('afficher-gestion-utilisateur-liste');
            }
        }

        return $this->render('gestion/utilisateurs/edition.html.twig', [
            'form'   => $form->createView(),
            'user'   => $user,
        ]);
    }
}
