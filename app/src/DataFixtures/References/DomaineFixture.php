<?php

namespace App\DataFixtures\References;

use App\Entity\References\Domaine;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DomaineFixture extends Fixture
{

    /**
     * Permet de créer une référence
     *
     * @param string $label
     * @return Domaine
     */
    private function referenceFactory(string $label, Domaine $domaineParent = null): Domaine
    {
        $domaine = new Domaine();
        $domaine->setLabel($label);
        $domaine->setDomaineParent($domaineParent);
        $domaine->setAjouteLe(new \DateTime());
        return $domaine;
    }

    /**
     * Permet de récupérer les données des références à créer
     * @return array|string[]
     */
    public function getReferences(): array
    {
        return [
            ['DOMAINE', ['Gestion du Domaine']],
            ['FISCALITE', ['Assiette et Taxation des Professionnels',
                            'Assiette et Taxation des particuliers',
                            'Contrôle fiscal et Contentieux',
                            'Foncier et Patrimoine',
                            'Recouvrement Particuliers, Professionnels et Produits divers']
            ],
            ['GESTION ET PILOTAGE DU SI', ['Sécurité',
                            'Infrastructures',
                            'Outils de production']
            ],
            ['GESTION PUBLIQUE', ['Comptabilité de l\'Etat',
                            'Dépenses de l\'Etat et Paie',
                            'Gestion comptable et financière des collectivités locales et des établissements publics',
                            'Gestion des Fonds déposés',
                            'Moyens de paiement',
                            'Retraites de l\'Etat et gestion des pensions',
                            'Valorisation, conseil fiscal et financier aux collectivités locales et établissements publics locaux']
            ],
            ['PILOTAGE', ['Audit, Risques et Contrôle de gestion',
                            'Communication']
            ],
            ['TRANSVERSE', ['Budget, Moyens et Logistique',
                            'Gestion des RH',
                            'Outillage',
                            'Référentiels partagés']
            ],
        ];
    }

    /**
     * Génère des références en base de données pour effectuer des tests
     */
    public function load(ObjectManager $manager)
    {
        // On crée les références
        foreach ($this->getReferences() as $labels) {
            $reference = $this->referenceFactory($labels[0]);
            $manager->persist($reference);
            foreach ($labels[1] as $label) {
                $sousReference = $this->referenceFactory($label, $reference);
                $manager->persist($sousReference);
            }
        }

        // On envoie les nouveaux objets en base de données
        $manager->flush();
    }
}
