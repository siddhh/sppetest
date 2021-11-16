<?php

namespace App\Tests\Gestion;

use App\Entity\References\Profil;
use App\Entity\User;
use App\Tests\UserWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class UsersTest extends UserWebTestCase
{
    
    // Nombre d'enregistrements retournés par page
    private const RECORDS_BY_PAGE = 20;

    // Liste des tests existants sur la liste des utilisateurs ajax
    private const FIRST_USER_LIST_TEST  = 'FIRST_USER_LIST_TEST';
    private const LAST_USER_LIST_TEST   = 'LAST_USER_LIST_TEST';

    /**
     * Teste d'accès à la partie gestion des utilisateurs
     * @dataProvider getAccesParRoles
     */
    public function testGestionUsersControleDesAcces(?string $role, int $statusCode)
    {
        $client = static::getClientByRole($role);
        // Pour chaque role, on teste l'accès sur l'ensemble des routes
        foreach (['/gestion/utilisateurs', '/gestion/utilisateur/creation', '/gestion/utilisateur/3/modification', '/ajax/gestion/utilisateurs/recherche'] as $servicePath) {
            $client->request(Request::METHOD_GET, $servicePath);
            $this->assertEquals($statusCode, $client->getResponse()->getStatusCode());
            if ($statusCode === 200) {
                switch ($servicePath) {
                    case '/gestion/utilisateurs':
                        $this->assertPageTitleContains('Gestion des utilisateurs');
                        $this->assertSelectorTextContains('.page-title', 'Gestion des utilisateurs');
                        break;
                    case '/gestion/utilisateur/creation':
                        $this->assertPageTitleContains('Création d\'un utilisateur');
                        break;
                    case '/gestion/utilisateur/3/modification':
                        $this->assertPageTitleContains('Mise à jour de l\'utilisateur');
                        break;
                    case '/ajax/gestion/utilisateurs/recherche':
                        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
                        break;
                }
            }
        }
    }

    /**
     * Teste si on peut creer un nouvel utilisateur après s'être connecté en administrateur
     */
    public function testCreerUser()
    {
        // connexion au compte administrateur
        $client = static::getClientByRole(Profil::ROLE_ADMIN);
        // on tente de récupérer la page de creation d'un utilisateur
        $crawler = $client->request('GET', '/gestion/utilisateur/creation');
        $this->assertResponseIsSuccessful();
        // on tente de valider le formulaire
        $form = $crawler->selectButton('Enregistrer l\'utilisateur')->form();
        $form->setValues([
            'user[prenom]'              => 'monPrenom',
            'user[nom]'                 => 'monNom',
            'user[motdepasseDisplayed]' => 'azerty',
            'user[motdepasseUpdate]'    => 1,
            'user[balp]'                => 'monPrenom.monNom@domain.tld',
        ]);
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertPageTitleContains('Gestion des utilisateurs');
        $userRepository = self::getEm()->getRepository(User::class);
        $user = $userRepository->findOneBy([], ['id' => 'DESC']);
        $this->assertEquals($user->getLogin(), 'monprenom.monnom');
        $this->assertEquals($user->getBalp(), 'monprenom.monnom@domain.tld');
    }

    /**
     * Teste si on peut modifier un utilisateur après s'être connecté en administrateur
     */
    public function testModificationUser()
    {
        // connexion au compte administrateur
        $client = static::getClientByRole(Profil::ROLE_ADMIN);
        // on tente de récupérer la page de modification d'utilisateur
        $userRepository = self::getEm($client)->getRepository(User::class);
        $user = $userRepository->findOneBy(['supprimeLe' => null], ['id' => 'DESC']);
        $userId = $user->getId();
        $crawler = $client->request('GET', "/gestion/utilisateur/{$userId}/modification");
        $this->assertResponseIsSuccessful();
        // on tente de valider le formulaire
        $form = $crawler->selectButton('Enregistrer l\'utilisateur')->form();
        $form->setValues([
            'user[prenom]'              => 'monPrenom',
            'user[nom]'                 => 'monNom',
            'user[motdepasseDisplayed]' => 'azerty',
            'user[motdepasseUpdate]'    => 1,
            'user[balp]'                => 'monPrenom.monNom@domain.tld',
        ]);
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertPageTitleContains('Gestion des utilisateurs');
        $userUpdated = $userRepository->find($userId);
        $this->assertEquals($userUpdated->getLogin(), 'monprenom.monnom');
        $this->assertEquals($userUpdated->getBalp(), 'monprenom.monnom@domain.tld');
    }

    /**
     * Test si on peut supprimer un utilisateur après s'être connecté en administrateur
     */
    public function testSuppressionUser()
    {
        // connexion au compte administrateur
        $client = static::getClientByRole(Profil::ROLE_ADMIN);
        // on tente de récupérer la page de modification d'utilisateur
        $userRepository = self::getEm($client)->getRepository(User::class);
        $user = $userRepository->findOneBy(['supprimeLe' => null], ['id' => 'DESC']);
        $userId = $user->getId();
        $crawler = $client->request('GET', "/gestion/utilisateur/{$userId}/modification");
        $this->assertResponseIsSuccessful();
        // on tente de valider le formulaire
        $form = $crawler->selectButton('Supprimer l\'utilisateur')->form();
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertPageTitleContains('Gestion des utilisateurs');
        $userDeleted = $userRepository->find($userId);
        $this->assertNotNull($userDeleted->getSupprimeLe());
    }

    /**
     * Teste le résultat de la liste des utilisateurs (ajax) dans divers conditions
     * @dataProvider getUsersListTests
     */
    public function testAjaxUserListe(string $testType, string $search = null)
    {
        $client = static::getClientByRole(Profil::ROLE_ADMIN);
        $userRepository = self::getEm()->getRepository(User::class);
        // On récupère la liste des utilisateurs attendus pour ce test à partir de la base de données
        if (!empty($search)) {
            $users = $userRepository->createQueryBuilder('u')
                ->select('u', 's')
                ->join('u.services', 's')
                ->where('u.supprimeLe IS NULL')
                ->andWhere('u.nom LIKE :search OR u.prenom LIKE :search OR u.balp LIKE :search')->setParameter('search', '%' . str_replace(['\\', '_', '%'], ['\\\\', '\\_', '\\%'], $search) . '%')
                ->orderBy('u.nom', 'ASC')
                ->addOrderBy('u.prenom', 'ASC')
                ->getQuery()
                ->getResult();
        } else {
            $users = $userRepository->findBy(['supprimeLe' => null], ['nom' => 'ASC', 'prenom' => 'ASC']);
        }
        $usersCount = count($users);
        $checkedUser = null;
        $currentPage = null;
        $indexInPage = null;
        $expectedUsersInCurrentPage = null;
        $lastPage = (int)(1 + floor(($usersCount - 1) / self::RECORDS_BY_PAGE));
        if (self::FIRST_USER_LIST_TEST === $testType) {
            $checkedUser = reset($users);
            $currentPage = 1;
            $indexInPage = 0;
            $expectedUsersInCurrentPage = $usersCount > self::RECORDS_BY_PAGE ? self::RECORDS_BY_PAGE : $usersCount;
            $client->request('GET', "/ajax/gestion/utilisateurs/recherche?search={$search}&supprimeLe=0&page=1");
        } elseif (self::LAST_USER_LIST_TEST === $testType) {
            $checkedUser = end($users);
            $currentPage = $lastPage;
            $indexInPage = $usersCount - (($currentPage - 1) * self::RECORDS_BY_PAGE) - 1;
            $expectedUsersInCurrentPage = $indexInPage + 1;
            $client->request('GET', "/ajax/gestion/utilisateurs/recherche?search={$search}&supprimeLe=0&page={$lastPage}");
        }
        // Vérifie le status renvoyé par le webservice
        $this->assertResponseIsSuccessful();
        // Teste si le premier utilisateur renvoyé par le webservice correspond à l'objet récupéré en base de données
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(
            $data['donnees'][$indexInPage],
            self::getExpectedUserData($checkedUser),
        );
        // Teste les infos de pagination renvoyées
        $this->assertSame(
            $data['pagination'],
            [
                'total'         => $usersCount,
                'parPage'       => self::RECORDS_BY_PAGE,
                'pages'         => $lastPage,
                'pageCourante'  => $currentPage,
            ]
        );
        // Test si le nombre d'utilisateurs renvoyés correspond aux règles de pagination
        $this->assertEquals(count($data['donnees']), $expectedUsersInCurrentPage);
    }

    /**
     * Retourne la structure attendue pour un utilisateur fournie par la liste des utilisateurs ajax
     * @param User $user
     * @return array
     */
    private static function getExpectedUserData(User $user): array
    {
        $services = [];
        foreach ($user->getServices() as $service) {
            $services[] = [
                'id'        => $service->getId(),
                'label'     => $service->getLabel(),
            ];
        }
        return             [
            'id'        => $user->getId(),
            'login'     => $user->getLogin(),
            'balp'      => $user->getBalp(),
            'nom'       => $user->getNom(),
            'prenom'    => $user->getPrenom(),
            'services'  => $services
        ];
    }

    /**
     * Retourne les tests d'accès à la gestion des utilisateurs.
     */
    public function getAccesParRoles(): array
    {
        $tests = [
            UserWebTestCase::ROLE_NON_CONNECTE => [
                null,
                302
            ],
            Profil::ROLE_INVITE => [
                Profil::ROLE_INVITE,
                302
            ],
            Profil::ROLE_ADMIN => [
                Profil::ROLE_ADMIN,
                200
            ],
            Profil::ROLE_ESI => [
                Profil::ROLE_ESI,
                403
            ],
            Profil::ROLE_BE => [
                Profil::ROLE_BE,
                403
            ],
            Profil::ROLE_STANDARD => [
                Profil::ROLE_STANDARD,
                403
            ],
            Profil::ROLE_CQMF => [
                Profil::ROLE_CQMF,
                403
            ],
            Profil::ROLE_SI2C => [
                Profil::ROLE_SI2C,
                403
            ],
        ];
        return $tests;
    }

    /**
     * Retourne la liste des tests à effectuer sur la liste des utilisateurs ajax
     */
    public function getUsersListTests(): array
    {
        return [
            self::FIRST_USER_LIST_TEST => [
                self::FIRST_USER_LIST_TEST,
                null
            ],
            self::LAST_USER_LIST_TEST => [
                self::LAST_USER_LIST_TEST,
                null
            ],
            self::FIRST_USER_LIST_TEST => [
                self::FIRST_USER_LIST_TEST,
                'b'
            ],
            self::FIRST_USER_LIST_TEST => [
                self::FIRST_USER_LIST_TEST,
                'b'
            ],
        ];
    }
}
