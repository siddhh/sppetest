<?php

namespace App\Tests\Gestion;

use App\Tests\UserWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\References\Domaine;
use App\Entity\References\Profil;

class DomaineTest extends UserWebTestCase
{
    /** @var string */
    protected static $urlDomaine = "/gestion/references/domaines";

    /** @var string */
    protected static $urlAjaxDomaine = "/ajax/gestion/references/domaines";

    /**
     * Test d'accès à la partie gestion des domaines
     * @dataProvider getAccesGestionParRoles1
     */
    public function testGestionDomainesControleDesAcces(string $role, int $statusCode)
    {
        $client = static::getClientByRole($role);
        $client->request(Request::METHOD_GET, static::$urlDomaine);
        $this->assertEquals($statusCode, $client->getResponse()->getStatusCode());
        if ($statusCode === 200) {
            $this->assertPageTitleContains('Gestion des Domaines');
            $this->assertSelectorTextContains('h2.page-title', 'Gestion du référentiel domaine');
        }
    }

    /**
     * Test création d'un domaine et sous-domaine
     * @dataProvider getAccesGestionParRoles2
     */
    public function testGestionDomainesCreation(string $role, int $statusCode)
    {
        $client = static::getClientByRole($role);
        $client->request(Request::METHOD_POST, static::$urlAjaxDomaine, ['domaine' => ['label' => $role]]);
        $response = $client->getResponse();
        $this->assertEquals($statusCode, $response->getStatusCode());
        if ($statusCode === 201) {
            $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
            $data = json_decode($response->getContent(), true);
            $nouvelId = $data['data']['nouvelId'];
            $serviceRepository = self::getEm()->getRepository(Domaine::class);
            $domaineCree = $serviceRepository->find($nouvelId);
            $this->assertEquals($domaineCree->getLabel(), $role);
            // Test création d'un sous-domaine
            $client->request(Request::METHOD_POST, static::$urlAjaxDomaine, ['domaine' => ['label' => 'sous-domaine', 'domaineParent' => $nouvelId]]);
            $response = $client->getResponse();
            $this->assertEquals(201, $response->getStatusCode());
            $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
            $data = json_decode($response->getContent(), true);
            $nouvelId = $data['data']['nouvelId'];
            $sousDomaineCree = $serviceRepository->find($nouvelId);
            $this->assertEquals($sousDomaineCree->getLabel(), 'sous-domaine');
            $this->assertEquals($sousDomaineCree->getDomaineParent()->getId(), $domaineCree->getId());
        }
    }

    /**
     * Test modification d'un domaine ayant un sous-domaine
     * @dataProvider getAccesGestionParRoles2
     */
    public function testGestionDomainesModification(string $role, int $statusCode)
    {
        $client = static::getClientByRole($role);
        $em = self::getEm();
        $serviceRepository = $em->getRepository(Domaine::class);
        $domaineCree = new Domaine();
        $domaineCree->setlabel('à modifier');
        $em->persist($domaineCree);
        $sousDomaineCree = new Domaine();
        $sousDomaineCree->setlabel('sous-domaine');
        $sousDomaineCree->setDomaineParent($domaineCree);
        $em->persist($sousDomaineCree);
        $em->flush();
        $client->request(Request::METHOD_PUT, static::$urlAjaxDomaine . '/' . $domaineCree->getId(), ['domaine' => ['label' => 'modifié']]);
        $response = $client->getResponse();
        $this->assertEquals($statusCode, $response->getStatusCode());
        if ($statusCode === 201) {
            $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
            $data = json_decode($response->getContent(), true);
            $this->assertEquals($domaineCree->getId(), $data['data']['supprimeId']);
            $em->refresh($domaineCree);
            $this->assertNotNull($domaineCree->getSupprimeLe());
            $nouvelId = $data['data']['nouvelId'];
            $domaineModifie = $serviceRepository->find($nouvelId);
            $this->assertEquals($domaineModifie->getLabel(), 'modifié');
            $em->refresh($sousDomaineCree);
            $this->assertEquals($sousDomaineCree->getDomaineParent()->getId(), $nouvelId);
        }
    }

    /**
     * Test suppression d'un domaine ayant un sous-domaine
     * @dataProvider getAccesGestionParRoles1
     */
    public function testGestionDomainesSuppression(string $role, int $statusCode)
    {
        $client = static::getClientByRole($role);
        $em = self::getEm();
        $serviceRepository = $em->getRepository(Domaine::class);
        $domaineCree = new Domaine();
        $domaineCree->setlabel('à supprimer');
        $em->persist($domaineCree);
        $sousDomaineCree = new Domaine();
        $sousDomaineCree->setlabel('sous-domaine');
        $sousDomaineCree->setDomaineParent($domaineCree);
        $em->persist($sousDomaineCree);
        $em->flush();
        $client->request(Request::METHOD_DELETE, static::$urlAjaxDomaine . '/' . $domaineCree->getId());
        $response = $client->getResponse();
        $this->assertEquals($statusCode, $response->getStatusCode());
        if ($statusCode === 200) {
            $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
            $data = json_decode($response->getContent(), true);
            $this->assertEquals($domaineCree->getId(), $data['data']['supprime_id']);
            $em->refresh($domaineCree);
            $this->assertNotNull($domaineCree->getSupprimeLe());
            $em->refresh($sousDomaineCree);
            $this->assertNotNull($sousDomaineCree->getSupprimeLe());
        }
    }

    public function getAccesGestionParRoles1(): array
    {
        return [
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
            ]
        ];
    }

    public function getAccesGestionParRoles2(): array
    {
        return [
            Profil::ROLE_INVITE => [
                Profil::ROLE_INVITE,
                302
            ],
            Profil::ROLE_ADMIN => [
                Profil::ROLE_ADMIN,
                201
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
            ]
        ];
    }
}
