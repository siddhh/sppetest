<?php

namespace App\DataFixtures\References;

use App\Entity\References\Profil;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProfilFixture extends Fixture
{

    /**
     * Permet de créer une référence
     *
     * @param string $label
     * @param int|null $priorite
     * @return Profil
     */
    private function profilFactory(string $label, int $priorite = 0): Profil
    {
        $profil = new Profil();
        $profil->setLabel($label);
        $profil->setPriorite($priorite);
        $profil->setAjouteLe(new \DateTime());
        return $profil;
    }

    /**
     * Permet de récupérer les données des références à créer
     * @return array|string[]
     */
    public function getReferences(): array
    {
        return [
            ['Administrateur SI-2A', 1000],
            ['ESI', 500],
            ['Bureau d\'Etudes', 200],
            ['Standard', 10],
            ['CQMF', 50],
            ['SI-2C', 100],
        ];
    }

    /**
     * Génère des références en base de données pour effectuer des tests
     */
    public function load(ObjectManager $manager)
    {
        // On crée les références
        foreach ($this->getReferences() as $referenceDonnees) {
            list($label, $priorite) = $referenceDonnees;
            $reference = $this->profilFactory($label, $priorite);
            $manager->persist($reference);
        }

        // On envoie les nouveaux objets en base de données
        $manager->flush();
    }
}
