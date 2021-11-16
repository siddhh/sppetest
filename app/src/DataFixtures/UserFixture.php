<?php

namespace App\DataFixtures;

use App\Entity\References\Profil;
use App\Entity\Service;
use App\Entity\User;
use App\DataFixtures\ServiceFixture;
use App\Utils\ChaineDeCaracteres;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture implements DependentFixtureInterface
{

    private const DOMAIN_NAME = 'dgfip.domain.tld';
    private const DEFAULT_PASSWORD = 'azerty';

    private UserPasswordHasherInterface $passwordHasher;

    /* login déjà générés */
    private $logins;

    /**
     * Récupère le UserPasswordHasherInterface pour hasher les mots de passe (injection de dépendances)
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->logins = [];
    }

    /**
     * On déclare les dépendances des fixtures dont nous avons besoin par la suite
     * (On est donc sûr que les données seront chargées)
     * @return array
     */
    public function getDependencies()
    {
        return [
            ServiceFixture::class,
        ];
    }

    /**
     * Génère des composants en base de données pour effectuer des tests
     */
    public function load(ObjectManager $manager)
    {
        // On initialise faker
        $faker = Faker\Factory::create('fr_FR');

        // On récupère certaines infos
        $userRepository = $manager->getRepository(User::class);
        $profils = $manager->getRepository(Profil::class)->findAll();
        $services = $manager->getRepository(Service::class)->findAll();

        // Génère un utilisateur de base pour chaque profil existant (en selectionnant le bon service)
        foreach ($profils as $profil) {
            $baseServiceLabel = 'Service ' . $profil->getLabel();
            $service = $manager->getRepository(Service::class)->createQueryBuilder('s')
                ->where('s.label = :serviceLabel')->setParameter('serviceLabel', $baseServiceLabel)
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
            $user = $this->getNewUser('FAKE', $profil->getLabel());
            $user->addService($service);
            $user->setLogin(mb_strtolower(str_replace([' ', 'd\''], ['-', ''], ChaineDeCaracteres::supprimeAccents($profil->getLabel()))));
            $user->setBalp($user->getLogin() . '@' . self::DOMAIN_NAME);
            $user->setMotdepasse($this->passwordHasher->hashPassword($user, self::DEFAULT_PASSWORD));
            $manager->persist($user);
        }

        // On récupère les utilisateurs déjà existants en base de données
        $users = $userRepository->findAll();
        foreach ($users as $user) {
            $this->logins[] = $user->getLogin();
        }

        // On crée 50 autres utilisateurs en leur affectant des services au hazard
        for ($i = 0; $i < 50; $i++) {
            // On récupère un nouvel utilisateur
            $user = $this->getNewUser($faker->lastName, $faker->firstName);
            $user->addService($this->getRandom($services));
            $user->setBalp($user->getLogin() . '@' . self::DOMAIN_NAME);
            // Certains users n'auront pas de mot de passe défini et ne pourront donc pas se connecter
            if (rand(0, 10) > 3) {
                $user->setMotdepasse($this->passwordHasher->hashPassword($user, self::DEFAULT_PASSWORD));
            }
            // On définit certains utilisateurs comme supprimés
            if (rand(0, 10) > 9) {
                $user->setSupprimeLe(new \DateTime());
            }
            // On persiste notre user
            $manager->persist($user);
        }
        // On tire la chasse
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

    /**
     * Méthode permettant de générer un utilisateur avec un login unique
     * @param string $lastName
     * @param string $firstName
     * @return User
     */
    private function getNewUser(string $lastName, string $firstName): User
    {
        $iterations = 1;
        $user = new User();
        $user->setNom(mb_strtoupper($lastName));
        $user->setPrenom(ucwords($firstName));
        $login = ChaineDeCaracteres::supprimeAccents(mb_strtolower(str_replace(' ', '_', $user->getPrenom() . '.' . $user->getNom())));
        while (in_array($login, $this->logins)) {
            if (1 === $iterations) {
                $login .= '-1';
            } else {
                $login = substr($login, 0, -strlen($iterations) - 1) . '-' . $iterations;
            }
            $iterations++;
        }
        $user->setLogin($login);
        $this->logins[] = $login;
        return $user;
    }
}
