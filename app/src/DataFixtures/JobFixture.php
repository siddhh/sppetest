<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Job;
use App\Entity\Machine;
use Faker;

class JobFixture extends Fixture implements DependentFixtureInterface
{
   /**
     * On déclare les dépendances des fixtures dont nous avons besoin par la suite
     * (On est donc sûr que les données seront chargées)
     * @return array
     */
    public function getDependencies()
    {
        return [
            MachineFixture::class
        ];
    }

    /**
     * Génère des jobs en base de données pour effectuer des tests
     */
    public function load(ObjectManager $manager)
    {
        // On initialise faker
        $faker = Faker\Factory::create('fr_FR');

        $machines = $manager->getRepository(Machine::class)->findAll();

        // On crée 30 jobs
        for ($i = 0; $i < 30; $i++) {
            $job = new Job();
            $job->setReference($faker->catchPhrase);
            $job->setNom($faker->name);
            $job->setLibelle($faker->optional($weight = 0.3)->firstNameMale);
            $randomIndex = rand(0, (count($machines) - 1));
            $job->setMachine($machines[$randomIndex]);

            // On persiste notre job
            $manager->persist($job);
        }
        // On envoie les nouveaux objets en base de données
        $manager->flush();
    }
}
