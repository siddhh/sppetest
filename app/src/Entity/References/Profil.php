<?php

namespace App\Entity\References;

use App\Repository\References\ProfilRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProfilRepository::class)
 */
class Profil extends Reference
{

    /**
     * Liste des roles reconnus par l'application
     */
    public const ROLE_ADMIN     = 'ROLE_ADMIN';     // Administrateur
    public const ROLE_ESI       = 'ROLE_ESI';       // Esi
    public const ROLE_BE        = 'ROLE_BE';        // Bureau d'études
    public const ROLE_STANDARD  = 'ROLE_STANDARD';  // Standard
    public const ROLE_CQMF      = 'ROLE_CQMF';      // CQMF
    public const ROLE_SI2C      = 'ROLE_SI2C';      // SI-2C / Copernic
    public const ROLE_INVITE    = 'ROLE_INVITE';    // invité (pas de service et de profil associés)

    /**
     * Tables permettant de mapper les roles associés au libellé du profil
     */
    private static $rolesMapping = [
        'Administrateur SI-2A'  => [ self::ROLE_ADMIN ],
        'ESI'                   => [ self::ROLE_ESI ],
        'Bureau d\'Etudes'      => [ self::ROLE_BE ],
        'Standard'              => [ self::ROLE_STANDARD ],
        'CQMF'                  => [ self::ROLE_CQMF ],
        'SI-2C'                 => [ self::ROLE_SI2C ],
    ];

    /**
     * @ORM\Column(type="smallint", options={"default": 0})
     */
    private $priorite;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Service", mappedBy="profil")
     */
    private $services;

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    public function getPriorite(): ?int
    {
        return $this->priorite;
    }

    public function setPriorite(int $priorite): self
    {
        $this->priorite = $priorite;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    /**
     * Retourne les roles associés au profil
     */
    public function getRoles(): array
    {
        $label = $this->getLabel();
        if (!empty(self::$rolesMapping[$label])) {
            return self::$rolesMapping[$label];
        }
        return [];
    }
}
