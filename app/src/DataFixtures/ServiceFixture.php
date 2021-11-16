<?php

namespace App\DataFixtures;

use App\Entity\Service;
use App\Entity\References\Profil;
use App\DataFixtures\References\ProfilFixture;
use App\Utils\ChaineDeCaracteres;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ServiceFixture extends Fixture implements DependentFixtureInterface
{

    /**
     * On déclare les dépendances des fixtures dont nous avons besoin par la suite
     * (On est donc sûr que les données seront chargées)
     * @return array
     */
    public function getDependencies()
    {
        return [
            ProfilFixture::class,
        ];
    }

    /**
     * Permet de créer un service de test avec divers informations de bases
     *
     * @param string $label
     * @param Profil $profil
     * @return Service
     */
    private function serviceFactory(string $label, Profil $profil): Service
    {
        $service = new Service();
        $service->setLabel($label);
        $service->setProfil($profil);
        $service->setBalf(mb_strtolower(str_replace([' ', 'd\''], ['-', ''], ChaineDeCaracteres::supprimeAccents($label))).'@dgfip.fr');
        return $service;
    }

    /**
     * Génère des services en base de données pour effectuer des tests
     */
    public function load(ObjectManager $manager)
    {
        // On récupère la liste des profils existants
        $profils = $manager->getRepository(Profil::class)->findAll();

        // Génère autant de services de base qu'il y a de profil
        foreach ($profils as $profil) {
            $manager->persist($this->serviceFactory('Service ' . $profil->getLabel(), $profil));
        }

        // Génère 25 services au hasard
        for ($i = 0; $i < 25; $i++) {
            $service = $this->serviceFactory(
                "Service." . uniqid(),
                $this->getRandom($profils)
            );
            $manager->persist($service);
        }

        // On envoie les nouveaux objets en base de données
        $manager->flush();
    }

    /**
     * Fonction permettant de récupérer de manière aléatoire une entrée parmi une collection passée en paramètre
     * Si le second paramètre est à true, alors, nous pouvons également avoir la valeur null (1/3 des cas)
     * @param $collection
     * @param bool $nullable
     * @return mixed
     */
    private function getRandom($collection, $nullable = false)
    {
        if ($nullable && rand(0, 2) === 0) {
            return null;
        } else {
            $randomIndex = rand(0, (count($collection) - 1));
            return $collection[$randomIndex];
        }
    }
}
