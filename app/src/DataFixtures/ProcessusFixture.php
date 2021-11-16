<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Application;
use App\Entity\Processus;
use App\Entity\Job;
use Faker;

class ProcessusFixture extends Fixture implements DependentFixtureInterface
{
    /**
     * On déclare les dépendances des fixtures dont nous avons besoin par la suite
     * (On est donc sûr que les données seront chargées)
     * @return array
     */
    public function getDependencies()
    {
        return [
            MachineFixture::class,
            ApplicationFixture::class
        ];
    }

    /**
     * Permet de récupérer une chaîne de planification aléatoire
     * @return string
     */
    public function getChaine(): string
    {
        $chaines = [
            'D=J|0,F=D|2|0',
            'D=J|0,F=J|0',
            'D=M|10|PR|JO|N|0,P=31123112,F=D|20|0',
            'D=J|,F=J|',
            'D=J|,F=J|,A=1',
            'D=S|VE|+1,P=01013112,F=J|0',
            'D=M|0203040506070809101112|02|JO|O|0,P=31123112,F=D|0|0,C=S|N|de GF|en septembre',
            'P=01013112,A=1'
        ];
        return $chaines[array_rand($chaines)];
    }

    /**
     * Génère des jobs en base de données pour effectuer des tests
     */
    public function load(ObjectManager $manager)
    {
        // On initialise faker
        $faker = Faker\Factory::create('fr_FR');

        $jobs = $manager->getRepository(Job::class)->findAll();
        $applications = $manager->getRepository(Application::class)->findAll();
        $maintenant = new \DateTime('NOW');

        // On crée 20 processus
        for ($i = 0; $i < 20; $i++) {
            $processus = new Processus();
            $processus->setReference($faker->country);
            $processus->setNom($faker->company);
            $processus->setLibelle($faker->optional($weight = 0.3)->jobTitle);
            $processus->setObjet($faker->optional($weight = 0.5)->text($maxNbChars = 1000));
            $randomIndex = rand(0, (count($applications) - 1));
            $processus->setApplication($applications[$randomIndex]);
            $nombreDeJobs = rand(0, 5);
            for ($j = 0; $j < $nombreDeJobs; $j++) {
                $randomIndex = rand(0, (count($jobs) - 1));
                $processus->addJob($jobs[$randomIndex]);
            }
            $processus->setChaineDePlanification($this->getChaine());
            $processus->setDescriptionPlanification($faker->text($maxNbChars = 100));
            if (rand(0, 1) == 1) {
                $processus->setDebutValidite((clone $maintenant)->add(new \DateInterval('P5D')));
                $processus->setFinValidite((clone $maintenant)->add(new \DateInterval('P11D')));
            }
            if (rand(0, 1) == 1) {
                $processus->setVersionImportee($faker->swiftBicNumber);
                $processus->setDateVersionImportee($faker->dateTime($max = 'now', $timezone = 'Europe/Paris'));
                $processus->setLibelle($faker->city);
            }

            // On persiste notre processus
            $manager->persist($processus);
        }

        // On envoie les nouveaux objets en base de données
        $manager->flush();
    }
}
