<?php

namespace App\Entity\References;

use App\Repository\References\EtatTraitementRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EtatTraitementRepository::class)
 */
class EtatTraitement extends Reference
{

}
