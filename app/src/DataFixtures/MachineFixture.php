<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Machine;
use Faker;

class MachineFixture extends Fixture
{
    /**
     * Génère des machines en base de données pour effectuer des tests
     */
    public function load(ObjectManager $manager)
    {
        // On initialise faker
        $faker = Faker\Factory::create('fr_FR');

        // On crée 10 machines
        for ($i = 0; $i < 10; $i++) {
            $machine = new Machine();
            $machine->setReference($faker->city);
            $machine->setLibelle($faker->optional($weight = 0.3)->firstNameFemale);

            // On persiste notre machine
            $manager->persist($machine);
        }

        // On envoie les nouveaux objets en base de données
        $manager->flush();
    }
}
