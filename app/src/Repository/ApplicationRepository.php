<?php

namespace App\Repository;

use App\Entity\Application;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Application|null find($id, $lockMode = null, $lockVersion = null)
 * @method Application|null findOneBy(array $criteria, array $orderBy = null)
 * @method Application[]    findAll()
 * @method Application[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ApplicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Application::class);
    }

    /**
     * @description Recherche des application par leurs libellés via une chaîne de caractère passée en paramètre.
     *
     * @param string|null $value
     * @return \Doctrine\ORM\Query
     */
    public function rechercheParLibelle(?string $value)
    {
        $libelle = '%' . mb_strtoupper($value) . '%';
        return $this->createQueryBuilder('a')
            ->where('UPPER(a.label) LIKE :libelle')
            ->andWhere('a.supprimeLe is NULL')
            ->orderBy('a.label', 'ASC')
            ->setParameter('libelle', $libelle)
            ->getQuery()
        ;
    }

    /**
    * @return Array[] Retourne les applications non archivées
    */
    public function listeApplications() : array
    {
        return $this->createQueryBuilder('a')
            ->addSelect('d', 'dp', 'se', 'sm')
            ->leftJoin('a.sousDomaine', 'd')
            ->leftJoin('d.domaineParent', 'dp')
            ->leftJoin('a.exploitant', 'se')
            ->leftJoin('a.MOE', 'sm')
            ->where('a.supprimeLe IS NULL')
            ->orderBy('a.label', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
