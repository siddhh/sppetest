<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    // Nom de la route affichange le formulaire d'authentification
    public const LOGIN_ROUTE = 'app-connexion';

    // Permet de faire des requètes sur la couche ORM
    private EntityManagerInterface $entityManager;

    // Interface permettant de récupérer une url à partir d'une route
    private UrlGeneratorInterface $urlGenerator;

    /**
     * Injection des dépendances
     * @param EntityManagerInterface $entityManager
     * @param UrlGeneratorInterface $urlGenerator
     * @return LoginFormAuthenticator
     */
    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Méthode en charge de l'authentification (appelée lorsque le formulaire d'authentification a été rempli)
     * @param Request $request
     * @return PassportInterface
     */
    public function authenticate(Request $request): PassportInterface
    {
        // Récupère les données du formulaire
        $login = $request->request->get('login', '');
        $isInvite = $request->request->get('signin_invite', false) !== false;

        // Mémorise le login utilisé pour se connecter en session (permet de pré-remplir le champ lors de la prochaine authentification)
        $request->getSession()->set(Security::LAST_USERNAME, $login);

        // Si l'utilisateur doit être connecté en tant qu'invité, il n'y a pas d'authentification nécessaire
        if ($isInvite) {
            return new SelfValidatingPassport(
                new UserBadge('invite'),
                [
                    new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
                ]
            );
        }

        // Vérifie si l'utilisateur indiqué existe en base de données et si son mot de passe est défini, sinon génère un message d'erreur adapté
        if (null === ($user = $this->entityManager->getRepository(User::class)->getUserByLogin($login))) {
            throw new UsernameNotFoundException("L'utilisateur {$login} ne semble pas être habilité à se connecter à cette application (utilisateur inconnu).");
        } elseif (null === $user->getPassword()) {
            throw new UsernameNotFoundException("L'utilisateur {$login} ne semble pas être habilité à se connecter à cette application (mot de passe non défini).");
        }

        // Ici devrait être mise en place l'authentification via LDAP...
        
        // Sinon
        return new Passport(
            new UserBadge($login),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->get('_csrf_token')),
            ]
        );
    }

    /**
     * Si l'authentification réussie, on redirige l'utilisateur vers la page appropriée
     * @param Request $request
     * @param TokenInterface $token
     * @param string $firewallName
     * @return Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): Response
    {
        // Redirige vers la page précédemment réclamée avant d'avoir été redirigé vers le formulaire d'authentification
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Par défaut, redirige vers la page d'accueil / tableau de bord de l'utilisateur
        return new RedirectResponse($this->urlGenerator->generate('afficher-tableau-de-bord'));
    }

    /**
     * Si l'authentification échoue, on adpate le message d'erreur
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Si l'erreur
        if (null !== ($previousException = $exception->getPrevious())) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $previousException);
        } else {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
            //dd(get_class($exception));
        }
        
        // Redirige l'utilisateur vers la page de login
        return new RedirectResponse($this->getLoginUrl($request));
    } 

    /**
     * Retourne l'url du formulaire d'authentification
     * @param Request $request
     * @return string
     */
    protected function getLoginUrl($request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
