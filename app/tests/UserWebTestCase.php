<?php

namespace App\Tests;

use App\Entity\References\Profil;
use App\Entity\User;
use App\Utils\Tests\TestBrowserToken;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\InMemoryUserProvider;

class UserWebTestCase extends WebTestCase
{

    const ROLE_NON_CONNECTE     = 'ROLE_NON_CONNECTE';

    /** @var EntityManager */
    protected static $entityManager;

    /** @var User[] */
    private static $cacheUserByLogins = [];

    /**
     * Fonction lancée au démarage des tests de la classe
     */
    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        self::$entityManager = self::$container->get('doctrine.orm.entity_manager');
        self::ensureKernelShutdown();
    }

    /**
     * Fonction permettant de se connecter à un compte avec des rôles bien défini
     *
     * @param KernelBrowser $client
     * @param Service $service
     */
    public static function loginAs(KernelBrowser &$client, UserInterface &$user) : void
    {
        $session = self::$container->get('session');

        $token = new TestBrowserToken($user->getRoles(), $user);
        $token->setAuthenticated(true);
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * Fonction permettant de créer un client déjà connecté avec un utilisateur
     *
     * @param User $service
     * @return KernelBrowser
     */
    public static function createClientLoggedAs(UserInterface &$user) : KernelBrowser
    {
        $client = static::createClient();
        static::loginAs($client, $user);
        return $client;
    }

    /**
     * Fonction retournant directement un client à partir d'un profil (si aucun profil fourni, on concidère que l'utilisateur n'est pas connecté)
     *
     * @param string|null $role
     * @return KernelBrowser
     */
    public static function getClientByRole(string $role = null) : KernelBrowser
    {
        $userLoginByRoles = [
            Profil::ROLE_ADMIN      => 'administrateur-si-2a',
            Profil::ROLE_ESI        => 'esi',
            Profil::ROLE_BE         => 'bureau-etudes',
            Profil::ROLE_STANDARD   => 'standard',
            Profil::ROLE_CQMF       => 'cqmf',
            Profil::ROLE_SI2C       => 'si-2c',
        ];
        if (null === $role) {
            return static::createClient();
        } elseif (Profil::ROLE_INVITE === $role) {
            $inMemoryUserProvider = new InMemoryUserProvider([
                'invite' => [
                    'roles' => [Profil::ROLE_INVITE],
                ],
            ]);
            $user = $inMemoryUserProvider->loadUserByIdentifier('invite');
            return static::createClientLoggedAs($user);
        } elseif (!empty($userLoginByRoles[$role])) {
            $user = self::getOneUserFromUserLogin($userLoginByRoles[$role]);
            return static::createClientLoggedAs($user);
        } else {
            throw new \Exception("Le rôle {$role} n\'est pas reconnu !");
        }
    }

    /**
     * Fonction permettant de récupérer l'entity manager de doctrine
     *
     * @param KernelBrowser $client
     * @return EntityManager
     */
    public static function getEm(KernelBrowser $client = null): EntityManager
    {
        if ($client instanceof KernelBrowser) {
            return $client->getContainer()->get('doctrine.orm.entity_manager');
        }
        return self::$entityManager;
    }

    /**
     * Fonction permettant de récupérer le repository associé à l'entité passée en paramètre
     *
     * @param string             $entityClass
     * @param KernelBrowser|null $client
     *
     * @return ObjectRepository
     */
    public static function getEmRepository(string $entityClass, KernelBrowser $client = null): ObjectRepository
    {
        return self::getEm($client)->getRepository($entityClass);
    }

    /**
     * Fonction permettant de récupérer un utilisateur selon les critères que l'on souhaite (actuellement seul le profil est géré)
     *
     * @param array $filters
     * @return User
     */
    public static function getOneUser(array $filters): User
    {
        $qb = self::getEmRepository(User::class)->createQueryBuilder('u')
            ->select('u', 's', 'p')
            ->join('u.services', 's')
            ->join('s.profil', 'p');
        if (!empty($filters['profilLabel'])) {
            $qb->andWhere('p.label = :profilLabel')->setParameter('profilLabel', $filters['profilLabel']);
        }
        if (!empty($filters['userLogin'])) {
            $qb->andWhere('u.login = :userLogin')->setParameter('userLogin', $filters['userLogin']);
        }
        return $qb->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * Fonction permettant de récupérer un utilisateur à partir d'un rôle
     */
    public static function getOneUserFromUserLogin(string $userLogin): User 
    {
        if (!isset(self::$cacheUserByLogins[$userLogin])) {
            self::$cacheUserByLogins[$userLogin] = self::getOneUser(['userLogin' => $userLogin]);
        }
        return self::$cacheUserByLogins[$userLogin];
    }

    /**
     * On ferme proprement les ressources utilisées
     */
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        self::$cacheUserByLogins = [];
        if (self::$entityManager instanceof EntityManager) {
            self::$entityManager->close();
            self::$entityManager = null;
        }
    }
}
