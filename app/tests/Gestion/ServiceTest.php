<?php

namespace App\Tests\Gestion;

use App\Entity\Service;
use App\Entity\References\Profil;
use App\Tests\UserWebTestCase;
use Symfony\Component\HttpFoundation\Request;

class ServiceTest extends UserWebTestCase
{
    /**
     * Teste d'accès à la partie gestion des services
     * @dataProvider getAccesGestionParRoles
     */
    public function testGestionServicesControleDesAcces(?string $role, int $statusCode)
    {
        $client = static::getClientByRole($role);
        // Pour chaque role, on teste l'accès sur l'ensemble des routes
        foreach (['/gestion/services', '/gestion/service/creation', '/gestion/service/1/modification'] as $servicePath) {
            $client->request(Request::METHOD_POST, $servicePath);
            $this->assertEquals($statusCode, $client->getResponse()->getStatusCode());
            if ($statusCode === 200) {
                switch ($servicePath) {
                    case '/gestion/services':
                        $this->assertPageTitleContains('Gestion des services');
                        $this->assertSelectorTextContains('.page-title', 'Gestion des services');
                        break;
                    case '/gestion/service/creation':
                        $this->assertPageTitleContains('Ajout d\'un service');
                        break;
                    case '/gestion/service/1/modification':
                        $this->assertPageTitleContains('Modification du service');
                        break;
                }
            }
        }
    }

     /**
     * Teste si on peut creer un nouveau service après s'être connecté en administrateur
     */
    public function testCreerService()
    {
        // connexion au compte administrateur
        $client = static::getClientByRole(Profil::ROLE_ADMIN);
        // on tente de récupérer la page de creation d'un service
        $crawler = $client->request('GET', '/gestion/service/creation');
        $this->assertResponseIsSuccessful();
        $profil = self::getEm()->getRepository(Profil::class)->findOneBy([], ['id' => 'ASC']);

        // on tente de valider le formulaire
        $form = $crawler->selectButton('Enregistrer le service')->form();
        $form->setValues([
            'service[label]'                => 'serviceTest',
            'service[profil]'               => $profil->getId(),
            'service[balf]'                 => 'service.test@dgfip.com',
        ]);

        // check server response:
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        // or more simply:
        $this->assertTrue($client->getResponse()->isSuccessful());


        $crawler = $client->submit($form);
        $this->assertTrue($client->getResponse()->isRedirection());
        $crawler = $client->followRedirect();
        $this->assertPageTitleContains('Gestion des services');

        // On récupère le service dans la base de données (dernière créée)
        $serviceRepository = self::getEm()->getRepository(Service::class);
        $service = $serviceRepository->findOneBy([], ['id' => 'DESC']);
        $this->assertEquals($service->getLabel(), 'serviceTest');
        $this->assertEquals($service->getProfil()->getId(), $profil->getId());
        $this->assertEquals($service->getBalf(), 'service.test@dgfip.com');
    }

     /**
     * Teste si on peut modifier une service après s'être connecté en administrateur
     */
    public function testModifierService()
    {
        // connexion au compte administrateur
        $client = static::getClientByRole(Profil::ROLE_ADMIN);
        // on tente de récupérer la page de modification d'une service
        $serviceRepository = self::getEm($client)->getRepository(Service::class);
        $service = $serviceRepository->findOneBy(['archiveLe' => null], ['id' => 'DESC']);
        $serviceId = $service->getId();
        $crawler = $client->request('GET', "/gestion/service/{$serviceId}/modification");
        $this->assertResponseIsSuccessful();

        // on tente de valider le formulaire
        $form = $crawler->selectButton('Enregistrer les modifications')->form();

        $profil = self::getEm()->getRepository(Profil::class)->findOneBy([], ['id' => 'ASC']);

        $form->setValues([
            'service[label]'                => 'serviceTestMaj',
            'service[profil]'               => $profil->getId(),
            'service[balf]'                 => 'service.testMaj@dgfip.com',
        ]);

        $crawler = $client->submit($form);

        $crawler = $client->followRedirect();
        $this->assertPageTitleContains('Gestion des services');

        // On récupère le service dans la base de données (dernière créée)
        $serviceRepository = self::getEm()->getRepository(Service::class);
        $service = $serviceRepository->findOneBy([], ['id' => 'DESC']);
        $this->assertEquals($service->getLabel(), 'serviceTestMaj');
        $this->assertEquals($service->getProfil()->getId(), $profil->getId());
        $this->assertEquals($service->getBalf(), 'service.testMaj@dgfip.com');
    }

    /**
     * Teste si on peut supprimer une service après s'être connecté en administrateur
     */
    public function testSupprimerService()
    {
        // connexion au compte administrateur
        $client = static::getClientByRole(Profil::ROLE_ADMIN);
        // on tente de récupérer la page de modification du service
        $serviceRepository = self::getEm($client)->getRepository(Service::class);
        $service = $serviceRepository->findOneBy(['archiveLe' => null], ['id' => 'DESC']);
        $serviceId = $service->getId();
        $crawler = $client->request('GET', "/gestion/service/{$serviceId}/modification");
        $this->assertResponseIsSuccessful();

        // on supprime le service
        $crawler = $client->request('GET', "/gestion/service/{$serviceId}/suppression");
        $crawler = $client->followRedirect();
        $this->assertPageTitleContains('Gestion des services');
        $serviceSupprimee = $serviceRepository->find($serviceId);
        $this->assertNotNull($serviceSupprimee->getArchiveLe());
    }

     /**
     * Retourne les tests d'accès à l'administration des services'.
     */
    public function getAccesGestionParRoles(): array
    {
        $tests = [
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
