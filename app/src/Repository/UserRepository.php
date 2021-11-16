<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Permet d'authentifier les utilisateurs non supprimés et aynat un mot de passe à partir de leur login
     *  cf: https://symfony.com/doc/current/security/user_provider.html#using-a-custom-query-to-load-the-user
     * @see UserLoaderInterface
     *
     * @param string $userIdentifier
     * @return User|null
    */
    public function loadUserByUsername(string $userIdentifier): ?User
    {
        return $this->getUserByLogin($userIdentifier, false);
    }

    /**
     * Cherche et retourne l'utilisateur demandé à partir de son login (typiquement dans un contexte d'authentification ou de recherche d'unicité)
     *  supprimeLe permet d'exclure ou inclure les utilisateurs supprimés
     * @param string $login
     * @param bool|null $supprimeLe
     * @param bool|null $withPassword
     * @return User|null
     */
    public function getUserByLogin(string $login, ?bool $supprimeLe = false, ?bool $withPassword = null): ?User
    {
        // On cherche un utilisateur à partir de son login
        $qb = $this->createQueryBuilder('u')
            ->select('u', 's', 'p')
            ->leftJoin('u.services', 's')
            ->leftJoin('s.profil', 'p')
            ->where('u.login = :login')->setParameter('login', $login);

        // Si le paramètre supprimeLe est précisé, on rajoute ce critère (par défaut les utilisateurs supprimés sont évincés de la recherche)
        if (null !== $supprimeLe) {
            $qb->andWhere('u.supprimeLe IS ' . ($supprimeLe ? 'NOT NULL' : 'NULL'));
        }
        // Si le parametre passwordNull est précisé,
        if (null !== $withPassword) {
            $qb->andWhere('u.motdepasse IS ' . ($withPassword ? 'NOT NULL' : 'NULL'));
        }

        // On retourne l'utilisateur trouvé, sinon rien.
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Cherche un User par balp (utilisé pour tester l'unicité)
     * @param string $login
     * @param bool|null $supprimeLe
     * @param bool|null $withPassword
     * @return User|null
     */
    public function getUserByBalp(string $balp, ?bool $supprimeLe = false, ?bool $withPassword = null): ?User
    {
        // On cherche un utilisateur à partir de son login
        $qb = $this->createQueryBuilder('u')
            ->select('u', 's', 'p')
            ->leftJoin('u.services', 's')
            ->leftJoin('s.profil', 'p')
            ->where('LOWER(u.balp) = LOWER(:balp)')->setParameter('balp', $balp);

        // Si le paramètre supprimeLe est précisé, on rajoute ce critère (par défaut les utilisateurs supprimés sont évincés de la recherche)
        if (null !== $supprimeLe) {
            $qb->andWhere('u.supprimeLe IS ' . ($supprimeLe ? 'NOT NULL' : 'NULL'));
        }
        // Si le parametre passwordNull est précisé,
        if (null !== $withPassword) {
            $qb->andWhere('u.motdepasse IS ' . ($withPassword ? 'NOT NULL' : 'NULL'));
        }

        // On retourne l'utilisateur trouvé, sinon rien.
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Retourne une liste des identifiants utilisateurs à partir des filtres demandés
     * @param array $filters
     * @return Query
     */
    public function getQuerySearchUserIds(array $filters = []): Query
    {
        // Base de la requete
        $qb = $this->createQueryBuilder('u')
            ->select('partial u.{id}');

        // Si les utilisateurs retournés doivent être filtrés
        if (!empty($filters['search'])) {
            $qb->andWhere('LOWER(u.nom) LIKE LOWER(:search) OR LOWER(u.prenom) LIKE LOWER(:search)
                    OR LOWER(u.login) LIKE LOWER(:search) OR LOWER(u.balp) LIKE LOWER(:search)')
                ->setParameter('search', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $filters['search']) . '%');
        }
        if (isset($filters['supprimeLe'])) {
            $qb->andWhere('u.supprimeLe IS ' . ($filters['supprimeLe'] ? 'NOT NULL' : 'NULL'));
        }
        if (isset($filters['balp'])) {
            $qb->andWhere('LOWER(u.balp) = LOWER(:balp)')->setParameter('balp', $filters['balp']);
        }

        // Défini un ordre et la pagination demandés
        $qb->orderBy('u.nom', 'ASC')
            ->addOrderBy('u.prenom', 'ASC');

        // Retourne la query
        return $qb->getQuery();
    }

    /**
    * @return Array[] Retourne les utilisateurs non supprimés
    */
    public function listeUsers() : array
    {
        return $this->createQueryBuilder('u')
            ->where('u.supprimeLe IS NULL')
            ->orderBy('LOWER(u.nom)', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
