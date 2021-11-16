<?php

namespace App\DataFixtures;

use App\Entity\Application;
use App\Entity\Service;
use App\Entity\References\Domaine;
use App\DataFixtures\ServiceFixture;
use App\DataFixtures\References\DomaineFixture;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker;

class ApplicationFixture extends Fixture implements DependentFixtureInterface
{
    /**
     * On déclare les dépendances des fixtures dont nous avons besoin par la suite
     * (On est donc sûr que les données seront chargées)
     * @return array
     */
    public function getDependencies()
    {
        return [
            ServiceFixture::class,
            DomaineFixture::class
        ];
    }

    public function load(ObjectManager $manager)
    {
        // On initialise faker
        $faker = Faker\Factory::create('fr_FR');

        // On récupère certaines infos
        $services = $manager->getRepository(Service::class)->findAll();
        $domaines = $manager->getRepository(Domaine::class)->sousDomainesNonSupprimes();
        $dateSuppression = new \DateTime();

        // Génère 40 applications au hasard
        for ($i = 0; $i < 40; $i++) {
            $application = new Application();
            $label = strtoupper($faker->unique()->company);
            $application->setLabel($label);
            // Une chance sur 6 qu elle soit supprimée
            if (rand(0, 5) == 5) {
                $application->setSupprimeLe($dateSuppression);
            }
            $randomIndex = rand(0, (count($domaines) - 1));
            $application->setSousDomaine($domaines[$randomIndex]);
            $randomIndex = rand(0, (count($services) - 1));
            // Association avec les services Exploitant et MOE
            $application->setExploitant($services[$randomIndex]);
            if (rand(0, 1) == 1) {
                $randomIndex = rand(0, (count($services) - 1));
                $application->setMOE($services[$randomIndex]);
            }
            // Ajout du périmètre applicatif
            for ($j = 0; $j < rand(0, 10); $j++) {
                $application->addService($services[rand(0, count($services) - 1)]);
            }
            $manager->persist($application);
        }

        // On envoie les nouveaux objets en base de données
        $manager->flush();
    }
}
