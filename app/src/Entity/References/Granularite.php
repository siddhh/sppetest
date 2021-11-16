<?php

namespace App\Entity\References;

use App\Repository\References\GranulariteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GranulariteRepository::class)
 */
class Granularite extends Reference
{

}
