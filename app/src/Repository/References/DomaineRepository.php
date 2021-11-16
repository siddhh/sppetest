<?php

namespace App\Repository\References;

use App\Entity\References\Domaine;

class DomaineRepository extends ReferenceRepository
{
    protected $entityClass = Domaine::class;

    /**
    * liste les domaines et leurs sous-domaines
    * @return Domaine[]
    */
    public function liste(?bool $avecSupprime = false)
    {
        $queryBuilder = $this->createQueryBuilder('d');
        if (!$avecSupprime) {
            $queryBuilder = $queryBuilder->where('d.supprimeLe is NULL');
        }
        return $queryBuilder
            ->andWhere('d.domaineParent is NULL')
            ->leftJoin('d.sousDomaines', 'sd')
            ->orderBy('LOWER(d.label)', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * Retourne les domaines ayant un parent, donc les sous-domaines, et non-supprimés
    * @return Domaine[]
    */
    public function sousDomainesNonSupprimes()
    {
        return $this->createQueryBuilder('d')
            ->where('d.domaineParent IS NOT NULL')
            ->andWhere('d.supprimeLe is NULL')
            ->orderBy('d.label', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * Retourne les domaines parents (ayant au moins un sous-domaine) et non-supprimés
    * @return array[]
    */
    public function domainesParentsNonSupprimes(): ?array
    {
        return $this->createQueryBuilder('d')
            ->select('parent.label, parent.id')
            ->join('d.domaineParent', 'parent')
            ->where('d.domaineParent IS NOT NULL')
            ->andWhere('d.supprimeLe is NULL')
            ->groupBy('parent.id')
            ->orderBy('parent.label', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
