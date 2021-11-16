<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    /**
    * recherche si un libellé de service non archivé déjà utilisé avec casse différente
    * @return Service[]
    */
    public function libelleServiceDejaUtilise($champs)
    {
        return $this->createQueryBuilder('s')
            ->where('UPPER(s.label) = :val')
            ->setParameter('val', mb_strtoupper($champs['label']))
            ->andWhere('s.archiveLe IS NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * recherche si balf non archivée est déjà utilisée avec casse différente
    * @return Service[]
    */
    public function balfDejaUtilise($champs)
    {
        return $this->createQueryBuilder('s')
            ->where('UPPER(s.balf) = :val')
            ->setParameter('val', strtoupper($champs['balf']))
            ->andWhere('s.archiveLe IS NULL')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Forme une requête permettant de lister les services par un filtre particulier passé en paramètre
     *
     * @param string $filtre
     * @return Query
     */
    public function listeServicesFiltre(string $filtre = null): Query
    {
        $entityManager = $this->getEntityManager();
        $filtre = '%' . mb_strtolower($filtre) . '%';

        $query = $entityManager->createQuery(
            'SELECT
                PARTIAL s.{
                    id,
                    label,
                    balf,
                    archiveLe,
                    profil
                },
                PARTIAL p.{
                    id,
                    label
                }
            FROM App\Entity\Service s
            JOIN s.profil p
            WHERE (
                    lower(s.label) LIKE :filtre
                ) AND s.archiveLe IS null
            ORDER by s.label ASC'
        );
        $query->setParameter('filtre', $filtre);

        return $query;
    }
}
