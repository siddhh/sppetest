<?php

namespace App\Tests\Gestion;

use App\Entity\References\Domaine;
use App\Entity\References\Profil;
use App\Entity\Application;
use App\Entity\Service;
use App\Tests\UserWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ApplicationsTest extends UserWebTestCase
{
    
    // Nombre d'enregistrements retournés par page
    private const RECORDS_BY_PAGE = 20;

    /**
     * Teste d'accès à la partie gestion des applications
     * @dataProvider getAccesParRoles
     */
    public function testGestionApplicationsControleDesAcces(?string $role, int $statusCode)
    {
        $client = static::getClientByRole($role);
        // Pour chaque role, on teste l'accès sur l'ensemble des routes
        foreach (['/gestion/applications', '/gestion/application/creation', '/gestion/application/1/modification', '/ajax/gestion/applications/recherche'] as $servicePath) {
            $client->request(Request::METHOD_GET, $servicePath);
            $this->assertEquals($statusCode, $client->getResponse()->getStatusCode());
            if ($statusCode === 200) {
                switch ($servicePath) {
                    case '/gestion/applications':
                        $this->assertPageTitleContains('Gestion des applications');
                        $this->assertSelectorTextContains('.page-title', 'Gestion des applications');
                        break;
                    case '/gestion/application/creation':
                        $this->assertPageTitleContains('Création d\'une application');
                        break;
                    case '/gestion/application/modification':
                        $this->assertPageTitleContains('Modification d\'une application');
                        break;
                    case '/ajax/gestion/applications/recherche':
                        $this->assertTrue($client->getResponse()->headers->contains('Content-Type', 'application/json'));
                        break;
                }
            }
        }
    }

    /**
     * Teste si on peut creer un nouvel utilisateur après s'être connecté en administrateur
     */
    public function testCreerApplication()
    {
        // connexion au compte administrateur
        $client = static::getClientByRole(Profil::ROLE_ADMIN);
        // on tente de récupérer la page de creation d'une application
        $crawler = $client->request('GET', '/gestion/application/creation');
        $this->assertResponseIsSuccessful();

        // on tente de valider le formulaire
        $form = $crawler->selectButton('Enregistrer')->form();
        $exploitant = self::getEm()->getRepository(Service::class)->findOneBy([], ['id' => 'ASC']);
        $sousDomaine =  self::getEm()->getRepository(Domaine::class)->sousDomainesNonSupprimes()[0];
        $form->setValues([
            'application[label]'              => 'monApplication',
            'application[sousDomaine]'  =>  $sousDomaine->getId(),
            'application[exploitant]'    => $exploitant->getId(),
            'application[MOE]'                => $exploitant->getId(),
        ]);
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();
        $this->assertPageTitleContains('Gestion des applications');

        // On récupère l'application dans la base de données (dernière créée)
        $applicationRepository = self::getEm()->getRepository(Application::class);
        $application = $applicationRepository->findOneBy([], ['id' => 'DESC']);
        $this->assertEquals($application->getLabel(), 'MONAPPLICATION');
        $this->assertEquals($application->getsousDomaine(), $sousDomaine);
        $this->assertEquals($application->getExploitant(), $exploitant);
        $this->assertEquals($application->getMOE(), $exploitant);
    }

     /**
     * Teste si on peut modifier une application après s'être connecté en administrateur
     */
    public function testModifierApplication()
    {
        // connexion au compte administrateur
        $client = static::getClientByRole(Profil::ROLE_ADMIN);
        // on tente de récupérer la page de modification d'une application
        $applicationRepository = self::getEm($client)->getRepository(Application::class);
        $application = $applicationRepository->findOneBy(['supprimeLe' => null], ['id' => 'DESC']);
        $applicationId = $application->getId();
        $crawler = $client->request('GET', "/gestion/application/{$applicationId}/modification");
        $this->assertResponseIsSuccessful();

        // on tente de valider le formulaire
        $form = $crawler->selectButton('Enregistrer')->form();
        $exploitant = self::getEm()->getRepository(Service::class)->findOneBy([], ['id' => 'ASC']);
        $sousDomaine =  self::getEm()->getRepository(Domaine::class)->sousDomainesNonSupprimes()[0];
        $form->setValues([
            'application[label]'              => 'monApplicationMAJ',
            'application[sousDomaine]'  =>  $sousDomaine->getId(),
            'application[exploitant]'    => $exploitant->getId(),
            'application[MOE]'                => $exploitant->getId(),
        ]);
        $crawler = $client->submit($form);
        $crawler = $client->followRedirect();/* */
        $this->assertPageTitleContains('Gestion des applications');

        // On récupère l'application en utilisant la base de données
        $applicationMAJ = $applicationRepository->find($applicationId);
        $this->assertEquals($applicationMAJ->getLabel(), 'MONAPPLICATIONMAJ');
        $this->assertEquals($applicationMAJ->getsousDomaine()->getId(), $sousDomaine->getId());
        $this->assertEquals($applicationMAJ->getExploitant()->getId(), $exploitant->getId());
        $this->assertEquals($applicationMAJ->getMOE()->getId(), $exploitant->getId());
    }

    /**
     * Teste si on peut supprimer une aplication après s'être connecté en administrateur
     */
    public function testSupprimerApplication()
    {
        // connexion au compte administrateur
        $client = static::getClientByRole(Profil::ROLE_ADMIN);
        // on tente de récupérer la page de modification d'application
        $applicationRepository = self::getEm($client)->getRepository(Application::class);
        $application = $applicationRepository->findOneBy(['supprimeLe' => null], ['id' => 'DESC']);
        $applicationId = $application->getId();
        $crawler = $client->request('GET', "/gestion/application/{$applicationId}/modification");
        $this->assertResponseIsSuccessful();

        // on supprime l'application
        $crawler = $client->request('GET', "/gestion/application/{$applicationId}/suppression");
        $crawler = $client->followRedirect();
        $this->assertPageTitleContains('Gestion des applications');
        $applicationSupprimee = $applicationRepository->find($applicationId);
        $this->assertNotNull($applicationSupprimee->getSupprimeLe());
    }

    /**
     * Teste le résultat de la liste des applications (ajax)
     */
    public function testAjaxApplicationListe()
    {
        $client = static::getClientByRole(Profil::ROLE_ADMIN);
        $applicationRepository = self::getEm($client)->getRepository(Application::class);
        $application = $applicationRepository->findOneBy(['supprimeLe' => null], ['id' => 'DESC']);
        $libelle = $application->getLabel();
        $client->Request(
            'GET',
            '/ajax/gestion/applications/recherche',
            [
                'Saisie' => $libelle,
                'page' => '1'
            ]
        );
        $this->assertResponseIsSuccessful();
        // Teste si la première application renvoyée par le webservice correspond à l'objet récupéré en base de données
        // c'est à dire que le libellé renvoyé inclue la chaîne de caractère saisie
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(
            str_contains($data['donnees'][0]['Libelle'], $libelle),
            true
        );
        // Teste les infos de pagination renvoyées
        $applis = $applicationRepository->rechercheParLibelle($libelle)->getResult();
        $nombreDAppli = count($applis);
        $nombrePages = (int)(1 + floor(($nombreDAppli - 1) / 10));
        $this->assertSame(
            $data['pagination'],
            [
                'total'         => $nombreDAppli,
                'parPage'       => self::RECORDS_BY_PAGE,
                'pages'         => $nombrePages,
                'pageCourante'  => 1,
            ]
        );
        // Teste si le nombre d'applications renvoyées correspond aux règles de pagination
        $nombreDApplicationsAttendues = $nombreDAppli > 10 ? 10 : $nombreDAppli;
        $this->assertEquals(count($data['donnees']), $nombreDApplicationsAttendues);
    }

    /**
     * Retourne les tests d'accès à l'administration des applications'.
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
}
